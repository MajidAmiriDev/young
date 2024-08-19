<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'due_date', 'priority', 'status','user_id'];

    /**
     * متد سفارشی برای ایجاد وظیفه جدید
     *
     * @param array $data
     * @return Task
     */
    public static function createTask(array $data)
    {
        $data['user_id'] = auth()->id(); // افزودن شناسه کاربر وارد شده به داده‌ها

        return self::create($data);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
