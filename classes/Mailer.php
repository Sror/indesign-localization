<?php
require_once(CLASSES.'phpmailer/class.phpmailer.php');
Class Mailer {
	private $mail;
	private $host = MAIL_HOST;
	private $port = MAIL_PORT;
	private $username = MAIL_USER;
	private $password = MAIL_PASS;
	private $sender_address = MAIL_FROM;
	private $sender_name = MAIL_FROM_NAME;
	private $reply_address = MAIL_REPLY_TO;
	private $reply_name = MAIL_REPLY_NAME;
	private $wordwrap = MAIL_WORD_WRAP;
	private $is_html = false;
	private $is_smtp = true;
	private $smtp_auth = true;
	//option such as ssl
	private $smtp_secure = "tls";

	function  __construct() {
		$this->mail = new PHPMailer();
		if($this->is_smtp) $this->mail->IsSMTP();
		$this->mail->SMTPAuth = $this->smtp_auth;
		if(!empty($this->smtp_secure)) $this->mail->SMTPSecure = $this->smtp_secure;
		$this->mail->Host = $this->host;
		$this->mail->Port = $this->port;
		$this->mail->Username = $this->username;
		$this->mail->Password = $this->password;
		$this->mail->From = $this->sender_address;
		$this->mail->FromName = $this->sender_name;
		$this->mail->AddReplyTo($this->reply_address,$this->reply_name);
	}

	function  __destruct() {
		unset($this->mail);
	}

	public function set_host($host) {
		$this->host = $host;
	}

	public function set_port($port) {
		$this->port = $port;
	}

	public function set_username($username) {
		$this->username = $username;
	}

	public function set_password($password) {
		$this->password = $password;
	}

	public function set_sender_address($sender_address) {
		$this->sender_address = $sender_address;
	}

	public function set_sender_name($sender_name) {
		$this->sender_name = $sender_name;
	}

	public function set_reply_address($reply_address) {
		$this->reply_address = $reply_address;
	}

	public function set_reply_name($reply_name) {
		$this->reply_name = $reply_name;
	}

	public function set_wordwrap($words) {
		$this->wordwrap = $words;
	}

	public function set_html($boolean) {
		$this->is_html = $boolean;
	}

	public function set_smtp($boolean) {
		$this->is_smtp = $boolean;
	}

	public function set_smtp_auth($boolean) {
		$this->smtp_auth = $boolean;
	}

	public function set_smtp_secure($smtp_secure) {
		$this->smtp_secure = $smtp_secure;
	}

	public function send_mail($name, $address, $subject, $body, $cc=array(), $attachments=array()) {
		$this->mail->Subject = $subject;
		$this->mail->WordWrap = $this->wordwrap;
		$this->mail->Body = $body;
		if($this->is_html) $this->mail->MsgHTML($body);
		$this->mail->AddAddress($address,$name);
		foreach($cc as $cc_address=>$cc_name) {
			$this->mail->AddCC($cc_address,$cc_name);
		}
		if(is_array($attachments)) {
			foreach($attachments as $attachment) {
				$this->mail->AddAttachment($attachment);
			}
		}
		$this->mail->IsHTML($this->is_html);
		return $this->mail->Send();
	}
}