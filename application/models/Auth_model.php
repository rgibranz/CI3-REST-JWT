<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public function get_by_username($username)
    {
        $where = ['username' => $username];
        return $this->db->get_where('user', $where)->row_array();
    }

    public function register_user($data)
    {
        $this->db->insert('user', $data);
        return $this->db->affected_rows();
    }
}
