<?php
namespace src\handlers;

use \src\models\User;
use \src\models\UserRelation;


class UserHandler{
    public static function checkLogin(){
        if(!empty($_SESSION['token'])){
            $token = $_SESSION['token'];

            $data = User::select()->where('token', $token)->one();
            if(count($data)>0){
                $loggedUser = new User();
                $loggedUser->id = $data['id'];
                $loggedUser->name = $data['name'];
                $loggedUser->avatar = $data['avatar'];

                return $loggedUser;
            }
        }
        
        return false;
    }

    public static function verifyLogin($email, $password){
        $user = User::select()->where('email', $email)->one();
        if($user){
            if(password_verify($password, $user['password'])){
                $token = md5(time().rand(0,9999).time());
                User::update()
                    ->set('token', $token)
                    ->where('email', $email)
                ->execute();
                return $token;
            }
        }
        return false;
    }
    public function idExists($id){
        $user = User::select()->where('id', $id)->one();
        return $user ? true : false;
    }

    public function emailExists($email){
        $user = User::select()->where('email', $email)->one();
        return $user ? true : false;
    }


    public function getUser($id, $full = false){
        $data = User::select()->where('id', $id)->one();

        if($data){
            $user = new User();
            $user->id = $data['id'];
            $user->name = $data['name'];
            $user->birthdate = $data['birthdate'];
            $user->city = $data['city'];
            $user->work = $data['work'];
            $user->avatar = $data['avatar'];
            $user->cover = $data['cover'];

            if($full){
                $user->followers= [];
                $user->following = [];
                $user->photos = [];

                //follwers
                $followers = UserRelation::select()->where('user-to', $id)->get();
                foreach($followers as $follwer){
                    $userData = User::select()->where('id', $follwer['user_from'])->one();
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->follwers[] = $newUser;
                }
                //follwing
                $following = UserRelation::select()->where('user-from', $id)->get();
                foreach($following as $follwer){
                    $userData = User::select()->where('id', $follwer['user_to'])->one();
                    
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->following[] = $newUser;
                }
                //photos
            }

            return $user;
        }

        return false;
    }

    public function addUser($name, $email, $password, $birthdate){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = md5(time().rand(0,9999).time());
        User::insert([
            'email' => $email,
            'password' => $hash,
            'name' => $name,
            'birthdate' => $birthdate,
            'token' => $token
        ])->execute();
    }
}