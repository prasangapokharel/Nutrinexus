<?php
namespace App\Core;

/**
 * Session class
 * Handles session management
 */
class Session
{
    /**
     * Start the session
     *
     * @return void
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set a session variable
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session variable exists
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session variable
     *
     * @param string $key
     * @return void
     */
    public static function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Clear all session variables
     *
     * @return void
     */
    public static function clear()
    {
        $_SESSION = [];
    }

    /**
     * Destroy the session
     *
     * @return void
     */
    public static function destroy()
    {
        // Unset all session variables
        $_SESSION = [];
        
        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Finally, destroy the session.
        session_destroy();
    }

    /**
     * Regenerate the session ID
     *
     * @param bool $deleteOldSession
     * @return bool
     */
    public static function regenerate($deleteOldSession = true)
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Set a flash message
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function setFlash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get and clear flash message
     *
     * @return array|null
     */
    public static function getFlash()
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        
        return null;
    }

    /**
     * Check if a flash message exists
     *
     * @return bool
     */
    public static function hasFlash()
    {
        return isset($_SESSION['flash']);
    }
}
