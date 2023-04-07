<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShortUrlController extends Controller
{
    public function create(Request $request){

        $validator = Validator::make($request->all(), [
            'long_url' => 'required|string',
            'preferred_alias' => 'nullable|string|unique:short_urls|alpha_dash',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }

        if($request->get('long_url')){
            $baseUrl = url('/');
            $newUrl = ShortUrl::create([
                'long_url' =>  $request->get('long_url'),
            ]);
            
            if($newUrl){
                $generatedKey = base_convert($newUrl->id, 10, 36);

                if(!empty($request->get('preferred_alias'))){
                    $newUrl->update([
                        'generated_key' => $generatedKey,
                        'short_url' => $baseUrl.'/'.$request->get('preferred_alias'),
                        'preferred_alias' => $request->get('preferred_alias'),
                    ]);
                }else{
                    $newUrl->update([
                        'generated_key' => $generatedKey,
                        'short_url' => $baseUrl.'/'.$generatedKey,
                        'preferred_alias' => $request->get('preferred_alias'),
                    ]);
                }
               
            }

            return response()->json([
                'short_url'=> $newUrl->short_url
            ], 
            200);
        }
        
       
    }

    public function show($slug){

       $shortUrl = ShortUrl::where('preferred_alias', $slug)->orWhere('generated_key', $slug)->first();
 
       if($shortUrl){
          return redirect()->to(url($shortUrl->long_url));
       }

       return redirect()->to(url('/'));
    }
}
