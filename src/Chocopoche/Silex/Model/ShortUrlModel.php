<?php
namespace Chocopoche\Silex\Model;

use Chocopoche\Math\Bijection;

/**
 * Helper methods to interact with the database.
 *
 * @todo a proper doctrine orm, or not
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

    public function getByShortCode($short_code) {
        $id = $this->encoder->decode($short_code);

        return $this->getById($id);
    }

    public function getById($id) {
        $url = $this->db->fetchColumn('SELECT url FROM url WHERE id = ?', array($id));
        if (!$url) return false;
        $short_code = $this->encoder->encode($id);

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

        for ($i=0; $i < count($urls); $i++) { 
            $urls[$i]['short_code'] = $this->encoder->encode($urls[$i]['id']);
        }
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
     * Import the schema in the database
     */
    public function importSchema() {

        $sm = new \Doctrine\DBAL\Schema\Schema;
        $user = $sm->createTable("user");
        $user->addColumn("id",    "integer", array("unsigned" => true));
        $user->addColumn("email", "string",  array("length"   => 255));
        $user->setPrimaryKey(array("id"));

        $url = $sm->createTable("url");
        $url->addColumn("id",         "integer", array("unsigned" => true));
        $url->addColumn("user_id",    "integer", array("unsigned" => true, "notnull" => false));
        $url->addColumn("url",        "string",  array("length"   => 1024));
        $url->addColumn("created_at", "datetime");
        $url->addForeignKeyConstraint($user, array("user_id"), array("id"), array("onUpdate" => "CASCADE"));
        $url->setPrimaryKey(array("id"));

        $redirect = $sm->createTable("redirect");
        $redirect->addColumn("id",         "integer",  array("unsigned" => true));
        $redirect->addColumn("url_id",     "integer",  array("unsigned" => true));
        $redirect->addForeignKeyConstraint($url, array("url_id"), array("id"), array("onUpdate" => "CASCADE"));
        $redirect->addColumn("created_at", "datetime");
        $redirect->setPrimaryKey(array("id"));

        $queries = $sm->toSql($this->db->getDatabasePlatform()); // get queries to create this schema.

        foreach ($queries as $query) {
            $this->db->query($query);
        }
    }

    /**
     * Get a user id from its email. Creates a record if the email does not exist
     *
     * @param string $email The user email
     *
     * @return integer The user id
     */
    private function getUserId($email) {
        $id = $this->db->fetchColumn('SELECT id FROM user WHERE email = ?', array($email));

        if (!$id) {
            $this->db->insert('user', array('email' => $email));
            $id = $this->db->lastInsertId();
        }

        return $id;
    }
}

