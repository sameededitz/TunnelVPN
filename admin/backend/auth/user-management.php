<?php
session_start();
include_once '../controller/config.php';
include_once '../controller/user-crud.php';
$user = new userAuth();
if (isset($_POST['createuser'])) {
    $data = $_POST;
    unset($data['createuser']);

    $registeruser = $user->userRegister($data);
    if ($registeruser == true && is_bool($registeruser)) {
        $crud->displayMessage('User Created Successfully', 'success', '../../all-users.php');
    } else {
        $_SESSION['oldData'] = $data;
        $crud->displayMessage($registeruser, 'error', '../../add-user.php');
    }
}
if (isset($_POST['loginuser'])) {
    $data = $_POST;
    unset($data['loginuser']);
    unset($data['remember_me']);
    $remember_me = isset($_POST['remember_me']) ? true : false;

    $loginuser = $user->userLogin($data, $remember_me,false,true);
    if ($loginuser == true && is_int($loginuser)) {
        $_SESSION['login_status'] = true;
        $_SESSION['user_id'] = $loginuser;
        $_SESSION['login_role'] = 'admin';
        $crud->displayMessage('Login Successfully', 'success', '../../index.php');
        exit;
    } else {
        $_SESSION['oldData'] = $data;
        $crud->displayMessage($loginuser, 'error', '../../login.php');
        exit;
    }
}

if (isset($_POST['updateuser'])) {
    $data = $_POST;
    unset($data['updateuser']);
    unset($data['pass_status']);

    $user_id = $data['user_id'];

    $requiredfields = (isset($_POST['pass_status'])) ? array('username', 'email', 'new_password', 'confirm_password') : array('username', 'email');
    $checkdata = $crud->validateMissing($data, $requiredfields);
    if (!empty($checkdata)) {
        $crud->displayMessage($checkdata, 'error', '../../edit-user.php?user_id=' . $user_id);
        exit();
    }

    if (isset($_POST['pass_status'])) {
        if ($data['new_password'] != $data['confirm_password']) {
            $crud->displayMessage('Passwords Do not Match', 'error', '../../edit-user.php?user_id=' . $user_id);
            exit();
        }
        if (strlen($_POST['new_password']) < 8) {
            $crud->displayMessage('Password must be greater than 8 characters', 'error', '../../edit-user.php?user_id=' . $user_id);
            exit();
        }
        $passwordhash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $data['password'] = $passwordhash;
    }

    $validateField = $crud->validateFields($data);
    if (!empty($validateField)) {
        $crud->displayMessage($validateField, 'error', '../../edit-user.php?user_id=' . $user_id);
        exit();
    }

    $checkUsername = $crud->select('users', 'user_id', null, "`username`=?", array($data['username']));
    if (is_array($checkUsername)) {
        if ($data['user_id'] != $checkUsername['user_id']) {
            $crud->displayMessage('Username already exists', 'error', '../../edit-user.php?user_id=' . $user_id);
            exit();
        }
    }
    $checkemail = $crud->select('users', 'user_id', null, "`email`=?", array($data['email']));
    if (is_array($checkemail)) {
        if ($data['user_id'] != $checkemail['user_id']) {
            $crud->displayMessage('Email already exists', 'error', '../../edit-user.php?user_id=' . $user_id);
            exit();
        }
    }

    unset($data['new_password']);
    unset($data['confirm_password']);
    unset($data['user_id']);

    $updateUser = $crud->update('users', $data, "`user_id`=$user_id");
    if (is_bool($updateUser)) {
        $crud->displayMessage('User Updated Successfully', 'success', '../../all-users.php');
    } else {
        $crud->displayMessage($updateUser, 'info', '../../all-users.php');
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'deleteuser') {
    $user_id = $_GET['user_id'];
    $deleteUser = $crud->delete('users', "`user_id`=?", array($user_id));
    if (is_bool($deleteUser)) {
        $crud->displayMessage('User Deleted Successfully', 'success', '../../index.php');
    } else {
        $crud->displayMessage($deleteUser, 'info', '../../index.php');
    }
}
