<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Laratrust\Traits\LaratrustUserTrait;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,LaratrustUserTrait;

    public function notifications()
    {
        //المستلم
        return $this->belongsToMany(Notification::class, 'notification_users', 'user_id', 'notification_id')
        ->whereNull('notification_users.deleted_at')
        ->withTimestamps()
        ->withPivot(['deleted_at']);
    }

    public function senders()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id'); //المرسل
    }

    public function likers()
    {
        return $this->belongsToMany(Post::class, 'like_posts', 'user_id', 'post_id');
    }

    public function viewers()
    {
        return $this->belongsToMany(Post::class, 'seen_posts', 'user_id', 'post_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'user_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id', 'id');
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'user_id', 'doctor_id');
    }

    public function doctors_rating()
    {
        return $this->belongsToMany(Doctor::class,'doctor_ratings', 'uesr_id', 'doctor_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'person_id',
        'username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at',
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // public function sendPasswordResetNotification($token)
    // {

    //     $url = 'https://spa.test/reset-password?token=' . $token;

    //     $this->notify(new ResetPasswordNotification($url));
    // }

    // public static function createAccount($inputs)
    // {
    //     $rules = [
    //         'person_id' => 'required|unique:users',
    //         'email' => 'max:30|unique:users',
    //         'username' => 'required|max:15|unique:users',
    //         'password' => 'required|confirmed',
    //     ];
    //     $messages = ['person_id.unique' => 'this person has an account'];

    //     $vaildator = Validator::make($inputs, $rules, $messages);

    //     if ($vaildator->fails()) {

    //         return ['errors' => $vaildator->errors(), 'success' => false];

    //     } else {
    //         $user = new User();
    //         $user->username = $inputs['username'];
    //         $user->password = encrypt($inputs['password']);
    //         $user->email = isset($inputs['email']) ? $inputs['email'] : null;
    //         $user->person_id = $inputs['person_id'];
    //         $user->save();
    //         return ['success' => true, 'user' => $user];
    //     }
    // }
}
