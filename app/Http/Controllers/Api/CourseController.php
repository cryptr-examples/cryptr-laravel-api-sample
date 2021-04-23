<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
 
class CourseController extends Controller
{
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function index(Request $request)
   {
        error_log(serialize($request->session()->get('cryptr-user')));
        return response()->json(
            [array(
                "id" => 1,
                "user_id" =>
                "eba25511-afce-4c8e-8cab-f82822434648",
                "title" => "learn git",
                "tags" => ["colaborate", "git" ,"cli", "commit", "versionning"],
                "img" => "https://carlchenet.com/wp-content/uploads/2019/04/git-logo.png",
                "desc" => "Learn how to create, manage, fork, and collaborate on a project. Git stays a major part of all companies projects. Learning git is learning how to make your project better everyday",
                "date" => '5 Nov',
                "timestamp" => 1604577600000,
                "teacher" => array(
                    "name" => "Max",
                    "picture" => "https://images.unsplash.com/photo-1558531304-a4773b7e3a9c?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=634&q=80"
                )
            )], 200);
   }
 
   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function store(Request $request)
   {
       $course = Course::create($request->all());
 
       if ($course) {
           return response() -> json([
               'data' => $course
           ], 200);
       } else {
           return response() -> json([
               'error' => 'unprocessable course'
           ], 422);
       }
   }
 
   /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function show($id)
   {
       if (Course::where('id', $id)->exists()) {
 
           return Course::find($id)->toJson();
       } else {
           return response()->json([
               "error" => "course not found"
           ], 404);
       }
   }
 
   /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function update(Request $request, $id)
   {
       if (Course::where('id', $id)->exists()) {
           $course = Course::find($id);
 
           $course->date = is_null($request->date) ? $course->date : $request->date;
           $course->desc = is_null($request->desc) ? $course->desc : $request->desc;
           $course->img = is_null($request->img) ? $course->img : $request->img;
           $course->title = is_null($request->title) ? $course->title : $request->title;
           $course->save();
 
           return response()->json([
               'data' => $course
           ], 200);
         } else {
           return response()->json([
             "error" => "course not found"
           ], 404);
         }
   }
 
   /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function destroy($id)
   {
       if(Course::where('id', $id)->exists()) {
           $course = Course::find($id);
           $course->delete();
 
           return response()->json([
             "message" => "records deleted"
           ], 202);
         } else {
           return response()->json([
             "error" => "course not found"
           ], 404);
         }
   }
}
