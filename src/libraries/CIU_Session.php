<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
* CodeIgniter source modified for CIUnit
* 
* If you use MY_Session, change the paraent class.
*/

class CIU_Session extends CI_Session {

	/**
	 * Destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_destroy()
	{

		$this->CI->db->where('session_id', $this->session_id);
		$this->CI->db->where('ip_address', $this->ip_address);
		$this->CI->db->where('user_agent', $this->user_agent);
		$this->CI->db->delete($this->sess_table_name);

		// Kill the cookie: modified for CIUnit
		$array = array(
					$this->sess_cookie_name,
					addslashes(serialize(array())),
					($this->now - 31500000),
					$this->cookie_path,
					$this->cookie_domain,
					0
				);
		$this->CI->output->set_cookie($array);
	}

	// --------------------------------------------------------------------

	/**
	 * Write the session cookie
	 *
	 * @access	public
	 * @return	void
	 */
	function _set_cookie($cookie_data = NULL)
	{

		$expire = ($this->sess_expire_on_close === TRUE) ? 0 : $this->sess_expiration + time();

		// Set the cookie: modified for CIUnit
		$array = array(
					$this->sess_cookie_name,
					$this->session_id,
					$expire,
					$this->cookie_path,
					$this->cookie_domain,
					$this->cookie_secure
				);
		$this->CI->output->set_cookie($array);
	}
}

/* End of file CIU_Session.php */
/* Location: ./application/third_party/CIUnit/libraries/CIU_Session.php */