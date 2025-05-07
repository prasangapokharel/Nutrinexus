<?php
namespace App\Core;

/**
* Base controller class
* 
* All controllers extend this class
*/
class Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize any common controller functionality
    }

    /**
     * Load model
     *
     * @param string $model
     * @return object
     */
    public function model($model)
    {
        $modelClass = 'App\\Models\\' . $model;
        return new $modelClass();
    }

    /**
     * Load view
     *
     * @param string $view
     * @param array $data
     * @return void
     */
    public function view($view, $data = [])
    {
        if (file_exists(dirname(dirname(__FILE__)) . '/views/' . $view . '.php')) {
            extract($data);
            require_once dirname(dirname(__FILE__)) . '/views/' . $view . '.php';
        } else {
            die('View does not exist');
        }
    }

    /**
     * Redirect to a page
     *
     * @param string $page
     * @return void
     */
    public function redirect($page)
    {
        header('Location: ' . BASE_URL . '/' . $page);
        exit;
    }

    /**
     * Set flash message
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    public function setFlash($type, $message)
    {
        Session::setFlash($type, $message);
    }

    /**
     * Get POST data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Require user to be logged in
     *
     * @return void
     */
    public function requireLogin()
    {
        if (!Session::has('user_id')) {
            Session::set('redirect_url', $_SERVER['REQUEST_URI']);
            $this->setFlash('error', 'Please login to continue');
            $this->redirect('auth/login');
        }
    }

    /**
     * Require user to be admin
     *
     * @return void
     */
    public function requireAdmin()
    {
        if (!Session::has('user_id') || Session::get('user_role') !== 'admin') {
            $this->setFlash('error', 'You do not have permission to access this page');
            $this->redirect('');
        }
    }
}
