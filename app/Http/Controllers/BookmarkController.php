<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookmarkRequest;
use App\Models\Bookmark;

class BookmarkController extends Controller
{
    public function bookmarkSwitcherAndRetriever(BookmarkRequest $request)
    {
        $request->headers->set('Accept', 'application/json');
        $validated = $request->validated();
        
        if($request->isMethod('post')) {
            $githubId = $request->input('github_id', $request->query('github_id'));
            $resourceId = $request->input('resource_id', $request->query('resource_id'));
            $bookmark = Bookmark::where('github_id', $githubId)->where('resource_id', $resourceId)->first();
            if ($bookmark) {
                $bookmark->delete();
            } else {
                $bookmark = Bookmark::create($validated);
            }            
        }
        $bookmarks = Bookmark::where('github_id', $validated['github_id'])->get();
        return response()->json($bookmarks, 200);
    }
}


// TESTS : validar que el bookmark creado sea el último registro en migration bookmarks o que no aparezca en la migration en caso de delete
// validation : $github_id required/integer/exists in roles && reource_id required/integer/exists in resources

/*
endpoints bookmarks :

GET '/bookmarks'
recibe github_id
devuelve json con github_id y array de resource_id (todos los bookmarks del usuario)

POST '/bookmarks' (conmutador)
recibe github_id y resource_id
- si no existe en la tabla 'bookmarks' lo crea (clave UNIQUE compuesta por ambas foreign keys)
- si existe en la tabla lo elimina
devuelve json con github_id y array de resource_id (todos los bookmarks del usuario)
(entiendo que el front "refrescará" el listado completo de bookmarks del usuario
*/
