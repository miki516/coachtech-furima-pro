<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make(
            $input,
            [
                // ユーザー名：必須、20文字以内
                'name'                  => ['required', 'string', 'max:20'],

                // メール：必須、メール形式、重複不可
                'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

                // パスワード：必須、8文字以上、確認用と一致
                'password'              => ['required', 'string', 'min:8', 'confirmed'],

                // 確認用：必須、8文字以上
                'password_confirmation' => ['required', 'string', 'min:8'],
            ],
            [
                'name.required'                  => 'お名前を入力してください',
                'name.max'                       => 'お名前は20文字以内で入力してください',

                'email.required'                 => 'メールアドレスを入力してください',
                'email.email'                    => 'メールアドレスはメール形式で入力してください',
                'email.unique'                   => 'このメールアドレスは既に登録されています',

                'password.required'              => 'パスワードを入力してください',
                'password.min'                   => 'パスワードは8文字以上で入力してください',
                'password.confirmed'             => 'パスワードと一致しません',

                'password_confirmation.required' => '確認用パスワードを入力してください',
                'password_confirmation.min'      => '確認用パスワードは8文字以上で入力してください',
            ],
            [
                'name'                  => 'お名前',
                'email'                 => 'メールアドレス',
                'password'              => 'パスワード',
                'password_confirmation' => '確認用パスワード',
            ]
        )->validate();

        return User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
