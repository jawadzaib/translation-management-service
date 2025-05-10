<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *     title="Translation Management Service",
 *     version="1.0.0"
 * )
 *
 * @OA\Tag(
 *     name="Translations",
 *     description="Manage translations across locales"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format: Bearer {token}"
 * )
 */
class TranslationsController extends Controller
{
     /**
      * @OA\Get(
      *     path="/api/translations",
      *     tags={"Translations"},
      *     summary="List translations",
      *     security={{"bearerAuth":{}}},
      *     @OA\Parameter(name="tag", in="query", description="Filter by tag", @OA\Schema(type="string")),
      *     @OA\Parameter(name="key", in="query", description="Filter by key", @OA\Schema(type="string")),
      *     @OA\Parameter(name="value", in="query", description="Filter by value", @OA\Schema(type="string")),
      *     @OA\Response(response=200, description="List of translations")
      * )
      */
     public function index(Request $request)
     {
          $translations = Translation::with('tags')
               ->when($request->query('tag'), fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->where('name', $request->query('tag'))))
               ->when($request->query('key'), fn ($q) => $q->where('key', 'like', '%' . $request->query('key') . '%'))
               ->when($request->query('value'), fn ($q) => $q->where('value', 'like', '%' . $request->query('value') . '%'))
               ->paginate(20);

          return response()->json($translations);
     }

     /**
      * @OA\Post(
      *     path="/api/translations",
      *     tags={"Translations"},
      *     summary="Create a translation",
      *     security={{"bearerAuth":{}}},
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             @OA\Property(property="key", type="string", example="example.title"),
      *             @OA\Property(property="value", type="string", example="Example"),
      *             @OA\Property(property="locale", type="string", example="en"),
      *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"web", "mobile"})
      *         )
      *     ),
      *     @OA\Response(response=201, description="Translation created")
      * )
      */
     public function store(Request $request)
     {
          $data = $request->validate([
               'key' => 'required|string',
               'value' => 'required|string',
               'locale' => 'required|string|size:2',
               'tags' => 'array'
          ]);

          $translation = Translation::create($data);
          if (!empty($data['tags'])) {
               $tagIds = collect($data['tags'])->map(function ($tagName) {
                    return Tag::firstOrCreate(['name' => $tagName])->id;
               });
               $translation->tags()->sync($tagIds);
          }

          return response()->json($translation->load('tags'), 201);
     }

     /**
      * @OA\Get(
      *     path="/api/translations/export",
      *     tags={"Translations"},
      *     summary="Export translations as JSON",
      *     description="Export translations for a specific locale as a flat JSON object.",
      *     security={{"bearerAuth":{}}},
      *     @OA\Parameter(name="locale", in="query", required=false, @OA\Schema(type="string", example="en")),
      *     @OA\Response(response=200, description="Exported translations")
      * )
      */
     public function export(Request $request)
     {
          $locale = $request->query('locale', 'en');

          $callback = function () use ($locale) {
               echo '{';
               $first = true;

               DB::table('translations')
                    ->where('locale', $locale)
                    ->orderBy('id')
                    ->chunk(1000, function ($rows) use (&$first) {
                         foreach ($rows as $row) {
                              if (!$first) {
                              echo ',';
                              }
                              echo json_encode($row->key) . ':' . json_encode($row->value);
                              $first = false;
                         }
                    });

               echo '}';
          };

          return response()->stream($callback, 200, [
               'Content-Type' => 'application/json',
          ]);
     }

     /**
      * @OA\Get(
      *     path="/api/translations/{id}",
      *     tags={"Translations"},
      *     summary="View a translation",
      *     description="Get a single translation by ID.",
      *     security={{"bearerAuth":{}}},
      *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
      *     @OA\Response(response=200, description="Success"),
      *     @OA\Response(response=404, description="Not found")
      * )
      */
      public function show($id)
      {
          try {
              return response()->json(
                  Translation::with('tags')->findOrFail($id)
              );
          } catch (Exception $e) {
              return response()->json([
                  'message' => 'Translation not found.',
              ], 404);
          }
      }

     /**
      * @OA\Put(
      *     path="/api/translations/{id}",
      *     tags={"Translations"},
      *     summary="Update a translation",
      *     description="Update translation by ID.",
      *     security={{"bearerAuth":{}}},
      *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             @OA\Property(property="key", type="string", example="example.title"),
      *             @OA\Property(property="value", type="string", example="Updated Example"),
      *             @OA\Property(property="locale", type="string", example="en"),
      *             @OA\Property(property="tags", type="array", @OA\Items(type="web"))
      *         )
      *     ),
      *     @OA\Response(response=200, description="Updated successfully"),
      *     @OA\Response(response=404, description="Not found")
      * )
      */
     public function update(Request $request, $id)
     {
          $data = $request->validate([
               'key' => 'required|string',
               'value' => 'required|string',
               'locale' => 'required|string|size:2',
               'tags' => 'array'
          ]);

          $translation = Translation::findOrFail($id);
          $translation->update($data);

          if (!empty($data['tags'])) {
               $tagIds = collect($data['tags'])->map(fn ($tagName) => Tag::firstOrCreate(['name' => $tagName])->id);
               $translation->tags()->sync($tagIds);
          }

          return response()->json($translation->load('tags'));
     }

     /**
      * @OA\Delete(
      *     path="/api/translations/{id}",
      *     tags={"Translations"},
      *     summary="Delete a translation",
      *     description="Deletes a translation by ID.",
      *     security={{"bearerAuth":{}}},
      *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
      *     @OA\Response(response=204, description="Deleted successfully"),
      *     @OA\Response(response=404, description="Not found")
      * )
      */
     public function destroy($id)
     {
          Translation::findOrFail($id)->delete();
          return response()->noContent();
     }
}
