<?php
require_once(__DIR__ . '/../database/Database.php');
require_once(__DIR__ . '/../interface/iLogin.php');

class Login extends Database implements iLogin {
    
    private $username;
    private $password;

    public function __construct()
    {
        parent::__construct();
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // Start session if not started
        }
    }

    public function set_un_pwd($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }   
    
    public function check_user()
    {
        $at_deped = 1; // Only active employees can log in

        // Fetch user by username
        $sql = "SELECT * FROM tbl_employee WHERE emp_un = ? AND emp_at_deped = ?";
        $user = $this->getRow($sql, [$this->username, $at_deped]);

        if ($user) {
            // Verify hashed password
            if (password_verify($this->password, $user['emp_pass'])) {
                return $user; // Login successful
            } else {
                error_log("Login failed: Incorrect password for user " . $this->username);
                return false; // Incorrect password
            }
        } else {
            error_log("Login failed: User " . $this->username . " not found or inactive");
            return false; // User not found
        }
    }

    public function get_user_id()
    {
        $type = 1; // Regular user
        $at_deped = 1;

        $sql = "SELECT emp_id FROM tbl_employee WHERE emp_un = ? AND type_id = ? AND emp_at_deped = ?";
        return $this->getRow($sql, [$this->username, $type, $at_deped]);
    }

    public function user_session()
    {
        if (!isset($_SESSION['user_logged_in'])) {
            header('location: ../index.php');
            exit();
        }
    }

    public function user_logout()
    {
        session_unset();
        session_destroy();
        header('location: ../index.php');
        exit();
    }

    public function admin_session()
    {
        if (!isset($_SESSION['admin_logged_in'])) {
            header('location: ../index.php');
            exit();
        }
    }

    public function admin_logout()
    {
        session_unset();
        session_destroy();
        header('location: ../index.php');
        exit();
    }

    public function admin_data()
    {
        $at_deped = 1;
        if (!isset($_SESSION['admin_logged_in'])) {
            return false;
        }

        $id = $_SESSION['admin_logged_in'];

        $sql = "SELECT * FROM tbl_employee WHERE emp_id = ? AND emp_at_deped = ?";
        return $this->getRow($sql, [$id, $at_deped]);
    }
}

$login = new Login();
?>