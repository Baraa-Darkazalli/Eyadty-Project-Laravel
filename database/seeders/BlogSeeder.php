<?php

namespace Database\Seeders;

use App\Models\ExtraService;
use App\Models\LikePost;
use App\Models\Post;
use App\Models\SeenPost;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $this->createPosts(50);
       $this->assignLikes();
       $this->assignSeen();
    }
    public function createPosts($count=1)
    {
        Post::factory($count)->create();
    }
    public function assignLikes()
    {
        $faker=Factory::create();
        $user_ids=User::query()->pluck('id');
        $posts=Post::all();
        foreach($posts as $post)
        {
            $max=(User::count()*2);
            while($max!=0)
            {
                $user_id=$faker->randomElement($user_ids);
                if(LikePost::where('post_id',$post->id)->where('user_id',$user_id)->exists())
                {
                    $max--;
                    continue;
                }
                LikePost::insert(['post_id'=>$post->id,'user_id'=>$user_id]);
                SeenPost::firstOrCreate(['user_id'=>$user_id,'post_id'=>$post->id]);
                $max--;
            }
        }
    }
    public function assignSeen()
    {
        $faker=Factory::create();
        $user_ids=User::query()->pluck('id');
        $posts=Post::all();
        foreach($posts as $post)
        {
            $max=(User::count()*2);
            while($max!=0)
            {
                $user_id=$faker->randomElement($user_ids);
                if(SeenPost::where('post_id',$post->id)->where('user_id',$user_id)->exists())
                {
                    $max--;
                    continue;
                }
                SeenPost::firstOrCreate(['user_id'=>$user_id,'post_id'=>$post->id]);
                $max--;
            }
        }
    }

}
