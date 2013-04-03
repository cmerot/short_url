<?php
namespace Chocopoche\Silex\Model;

use Chocopoche\Math\Bijection;

/**
 * Helper methods to interact with the database
 * TODO a proper doctrine orm, or not
 */
class ShortUrlModel
{
    protected $db;
    protected $encoder;
    protected $prefix;
    protected $user;

    /**
     * Constructor
     *
     * @param Doctrine\DBAL\Connection $db The connection used to store url
     * @param object $encoder The encoder used to short/unshort ids
     */
    public function __construct(\Doctrine\DBAL\Connection $db, $encoder) {
        $this->db = $db;
        $this->encoder = $encoder;
    }

    /**
     * Proxy method for $this->encoder->encode()
     */
    public function encode($code) { return $this->encoder->encode($code); }

    /**
     * Proxy method for $this->encoder->decode()
     */
    public function decode($code) { return $this->encoder->decode($code); }


    public function getByShortCode($short_code) {
        $id = $this->decode($short_code);

        return $this->getById($id);
    }

    public function getById($id) {
        $url = $this->db->fetchColumn('SELECT url FROM url WHERE id = ?', array($id));
        if (!$url) return false;
        $short_code = $this->encode($id);

        return array(
            'id'         => $id,
            'url'        => $url,
            'short_code' => $short_code,
        );
    }

    /**
     * Returns the last shortened urls
     *
     * @param integer $count The number of url to return
     *
     * @return array The $count last shortened url in an associative array
     */
    public function getLastShorten($count = 1, $email = null) {
        if ($email) {
            $user_id = $this->getUserId($email);
            $urls  = $this->db->fetchAll('SELECT id, url FROM url WHERE user_id = ? ORDER BY id DESC LIMIT ?', array($user_id, $count));
        } 
        else {
            $urls  = $this->db->fetchAll('SELECT id, url FROM url ORDER BY id DESC LIMIT ?', array($count));
        }
        $model = $this;
        array_walk($urls, function(&$u) use ($model) {
            $u['short_code']    = $model->encode($u['id']);
        });

        return $urls;
    }

    /**
     * Returns the last times a short url has been hit
     * 
     * @param string  $short_code The encoded code that represents a record id
     * @param integer $count      The limit of records to retrieve
     * 
     * @return array The last $count redirects url in an associative array (key: created_at)
     */
    public function getLastRedirects($id, $count = 10) {
        $redirects = $this->db->fetchALL('SELECT created_at FROM redirect WHERE url_id = ? ORDER BY id DESC LIMIT ?', array($id, $count));

        return $redirects;
    }

    /**
     * Returns the number of redirects for an url
     * 
     * @param integer $id Represents a record id
     * 
     * @return integer The number of redirects
     */
    public function getRedirectCounter($id) {
        $count = $this->db->fetchColumn('SELECT COUNT(*) FROM redirect WHERE url_id = ?', array($id));

        return $count;
    }

    /**
     * Increments the hit counter of a shorten url
     * 
     * @param integer $id Represents a record id
     */
    public function incrementCounter($id) {
        $this->db->insert('redirect', array('url_id' => $id, 'created_at' => date('Y-m-d H:i:s')));
    }

    /**
     * Shortens and saves a long url
     *
     * @param string $long_url The long url to save
     *
     * @return integer The id of the newly created url
     */
    public function add($long_url, $email = null) {
        if (! $email) {
            $this->db->insert('url', array('url' => $long_url, 'created_at' => date('Y-m-d H:i:s')));
        } 
        else {
            $this->db->insert('url', array(
                'url'           => $long_url, 
                'created_at'    => date('Y-m-d H:i:s'),
                'user_id'       => $this->getUserId($email),
            ));
        }

        return $this->db->lastInsertId();
    }

    /**
     * Get a user id from its email. Creates a record if the email does not exist
     *
     * @param string $email The user email
     *
     * @return integer The user id
     */
    public function getUserId($email) {
        $id = $this->db->fetchColumn('SELECT id FROM user WHERE email = ?', array($email));

        if (!$id) {
            $this->db->insert('user', array('email' => $email));
            $id = $this->db->lastInsertId();
        }

        return $id;
    }
}

