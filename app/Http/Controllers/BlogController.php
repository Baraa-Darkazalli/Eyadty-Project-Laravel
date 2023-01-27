<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\ExtraService;
use App\Models\Post;
use App\Models\User;
use App\Traits\ApiResponderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    use ApiResponderTrait;

    public function isActive()//role:Patient|Admin|Doctor
    {
        $data = ExtraService::where([['service_name', '=', 'Blog'], ['is_active', '=', true]])->exists();

        return $this->okResponse($data);
    }

    public function addPost(Request $request)//role:Doctor
    {
        $data = $request->only(
            'post_subject',
            'body',
            'image'
        );
        $rules = [
            'post_subject' => 'required',
            'body' => 'required_without:image',
            'image' => 'required_without:body',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        } else {
            $blog = \App\Models\ExtraService::where('service_name', 'Blog')->first();
            if (! $blog->is_active) {
                return $this->badRequestResponse(__('msg.blog_is_disabled'));
            }
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $name = time().'.'.$image->extension();
                $path = $image->move('images', $name);
                $path = (string) $path;
            } else {
                $path = null;
            }
            $doctor_id = auth()->user()->person->employee->doctor->id;
            $post = new \App\Models\Post();
            $post->blog_id = $blog->id;
            $post->doctor_id = $doctor_id;
            $post->post_subject = $request->post_subject;
            $post->body = $request->body;
            $post->image = $path;
            $post->save();

            return $this->createdResponse(null, __('msg.post_created_successfully'));
        }
    }

    public function lastPost()//role:Admin-Patient-Doctor
    {
        $post = Post::latest()->first();
        if (! isset($post)) {
            return $this->okResponse(null, __('msg.there_are_no_posts'));
        }
        $post_date = $post->updated_at;
        $post_date = \Carbon\Carbon::parse($post_date)->format('Y-m-d');
        $publisher_name = $post->doctors->employee->person->name;
        $data = [
            'department_name' => $post->doctors->clinic->clinic_name->clinic_department->name,
            'id' => $post->id,
            'publisher_name' => $publisher_name->first_name.' '.$publisher_name->last_name,
            'post_title' => $post->post_subject,
            'image' => $post->image,
            'post_date' => $post_date,
            'likes' => $post->likers->count(),
            'views' => $post->viewers->count(),
            'is_like' => ($post->likers()->where('user_id', auth()->user()->id)->exists()) ? true : false,
        ];

        return $this->okResponse($data);
    }

    public function getBlogDepartments()//role:Patient-Admin-Doctor
    {
        //check if there are no posts
        if (empty(Post::all()->count())) {
            return $this->okResponse(null, __('msg.there_are_no_posts'));
        }
        $posts = Post::all();
        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->doctors->clinic->clinic_name->clinic_department->id,
                'name' => $post->doctors->clinic->clinic_name->clinic_department->name,
            ];
        }

        return $this->okResponse(array_unique($data, SORT_REGULAR), __('msg.all_departments_in_blog'));
    }

    public function searchBlogDepartments(Request $request)//role:Patient-Admin-Doctor
    {
        $posts_departments = Post::join('doctors','doctors.id','posts.doctor_id')
                                ->join('clinics','clinics.id','doctors.clinic_id')
                                ->join('clinic_names','clinic_names.id','clinics.clinic_name_id')
                                ->join('clinic_departments','clinic_departments.id','clinic_names.department_id')
                                ->select('clinic_departments.id', 'clinic_departments.name')
                                ->where('clinic_departments.name', 'LIKE', '%'.$request->input.'%')->distinct()->get();

        if (count($posts_departments) > 0) {
            return $this->okResponse($posts_departments);
        } else {
            return $this->okResponse(null, __('msg.input_not_found'));
        }
    }

    public function getPostsByDepartmentsId(Request $request)//role:Patient|Admin|Doctor
    {
        if (empty(Post::all()->count())) {
            return $this->okResponse(null, __('msg.there_are_no_posts'));
        }
        $department_id = $request->id;
        $posts = Post::all();
        foreach ($posts as $post) {
            if ($post->doctors->clinic->clinic_name->clinic_department->id != $department_id) {
                continue;
            }
            $data[] = [
                'id' => $post->id,
                'publisher_name' => $post->doctors->employee->person->name->first_name.' '.$post->doctors->employee->person->name->last_name,
                'post_title' => $post->post_subject,
                'image' => $post->image,
                'post_date' => $post->updated_at->format('Y-m-d'),
                'likes' => $post->likers->count(),
                'views' => $post->viewers->count(),
                'is_like' => ($post->likers()->where('user_id', auth()->user()->id)->exists()) ? true : false,
            ];
        }
        $data=$data??false;
        if(!$data)return $this->okResponse(null,__('msg.there_are_no_posts_in_this_department'));

        return $this->okResponse($data);
    }

    public function searchPostsByDepartmentsId(Request $request)//role:Patient|Admin|Doctor
    {
        $posts = Post::where('post_subject', 'LIKE', '%'.$request->input.'%')->get();

        foreach ($posts as $post) {
            if ($post->doctors->clinic->clinic_name->clinic_department->id != $request->department_id) {
                continue;
            }
            $data[] = [
                'id' => $post->id,
                'publisher_name' => $post->doctors->employee->person->name->first_name.' '.$post->doctors->employee->person->name->last_name,
                'post_title' => $post->post_subject,
                'image' => $post->image,
                'post_date' => $post->updated_at->format('Y-m-d'),
                'likes' => $post->likers->count(),
                'views' => $post->viewers->count(),
                'is_like' => ($post->likers()->where('user_id', auth()->user()->id)->exists()) ? true : false,
            ];
        }
        $data=$data??false;
        if(!$data)return $this->okResponse(null,__('msg.input_not_found'));

        return $this->okResponse($data);
    }

    public function getSinglePost(Request $request)//role:Patient|Admin|Doctor
    {
        $post_id = $request->id;
        $post = Post::find($post_id);
        if (! isset($post)) {
            return $this->badRequestResponse(__('msg.this_id_is_not_for_post'));
        }
        $post->viewers()->sync([auth()->user()->id], false);
        $data = [
            'content' => $post->body,
        ];

        return $this->okResponse($data);
    }

    public function likePost(Request $request)//role:Patient|Admin|Doctor
    {
        $post_id = $request->id;
        $post = Post::find($post_id);
        if (! isset($post)) {
            return $this->badRequestResponse(__('msg.this_id_is_not_for_post'));
        }
        if ($post->likers()->where('user_id', auth()->user()->id)->exists()) {
            $post->likers()->detach(auth()->user()->id);
        } else {
            $post->likers()->attach(auth()->user()->id);
        }

        $data = ['likes' => $post->likers->count()];

        return $this->okResponse($data);
    }

    public function editPost(Request $request)//role:Doctor
    {
        $data = $request->only(
            'id',
            'post_subject',
            'body',
            'image'
        );
        $rules = [
            'id' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->badRequestResponse($validator->errors()->all());
        }

        $blog = \App\Models\ExtraService::where('service_name', 'Blog')->first();
        if (! $blog->is_active) {
            return $this->badRequestResponse(__('msg.blog_is_disabled'));
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->extension();
            $path = $image->move('images', $name);
            $path = (string) $path;
        } else {
            $path = null;
        }
        $post = Post::find($request->id);
        $post->post_subject = ($request->post_subject) ?? $post->post_subject;
        $post->body = ($request->body) ?? $post->body;
        $post->image = ($path) ?? $post->image;
        $post->save();

        return $this->okResponse(null, __('msg.post_updated_successfully'));
    }

    public function deletePost(Request $request)//role:Doctor|Admin
    {
        $post_id = $request->id;
        $post = Post::find($post_id);
        if (! isset($post)) {
            return $this->badRequestResponse(__('msg.this_is_failed_id'));
        }
        $post->delete();

        return $this->okResponse(null, __('msg.post_deleted_successfully'));
    }

    public function getMyPosts()//role:Doctor
    {
        $doctor = User::find(auth()->user()->id)->person->employee->doctor;

        //check if there are no posts
        if (empty($doctor->posts->count())) {
            return $this->okResponse(null, __('msg.you_do_not_have_any_post'));
        }

        foreach ($doctor->posts as $post) {
            $data[] = [
                'id' => $post->id,
                'publisher_name' => $doctor->employee->person->name->first_name.' '.$doctor->employee->person->name->last_name,
                'post_title' => $post->post_subject,
                'image' => $post->image,
                'post_date' => $post->updated_at->format('Y-m-d'),
                'likes' => $post->likers->count(),
                'views' => $post->viewers->count(),
                'is_like' => ($post->likers()->where('user_id', auth()->user()->id)->exists()) ? true : false,
            ];
        }

        return $this->okResponse($data);
    }
}
