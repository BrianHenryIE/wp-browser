<?php
namespace Codeception\Module;

use BaconStringUtils\Slugifier;
use Codeception\Configuration as Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Driver\ExtendedDbDriver as Driver;
use PDO;
use PHPUnit_Framework_ExpectationFailedException;
use tad\WPBrowser\Generators\Comment;
use tad\WPBrowser\Generators\Post;
use tad\WPBrowser\Generators\User;

/**
 * An extension of Codeception Db class to add WordPress database specific
 * methods.
 */
class WPDb extends ExtendedDb {

	/**
	 * @var array
	 */
	protected $termKeys = [ 'term_id', 'name', 'slug', 'term_group' ];

	/**
	 * @var array
	 */
	protected $termTaxonomyKeys = [ 'term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count' ];

	/**
	 * @var array A list of tables that WordPress will nor replicate in multisite installations.
	 */
	protected $uniqueTables = [ 'blogs', 'blog_versions', 'registration_log', 'signups', 'site', 'sitemeta', 'users', 'usermeta', ];

	/**
	 * The module required configuration parameters.
	 *
	 * url - the site url
	 *
	 * @var array
	 */
	protected $requiredFields = array( 'url' );

	/**
	 * The module optional configuration parameters.
	 *
	 * tablePrefix - the WordPress installation table prefix, defaults to "wp".
	 * checkExistence - if true will attempt to insert coherent data in the database; e.g. an post with term insertion
	 * will trigger post and term insertions before the relation between them is inserted; defaults to false. update -
	 * if true have... methods will attempt an update on duplicate keys; defaults to true.
	 *
	 * @var array
	 */
	protected $config = array( 'tablePrefix' => 'wp_', 'checkExistence' => false, 'update' => true, 'reconnect' => false );
	/**
	 * The table prefix to use.
	 *
	 * @var string
	 */
	protected $tablePrefix = 'wp_';

	/**
	 * @var int The id of the blog currently used.
	 */
	protected $blogId = 0;

	/**
	 * Initializes the module.
	 *
	 * @return void
	 */
	public function _initialize() {
		if ( $this->config['dump'] && ( $this->config['cleanup'] or ( $this->config['populate'] ) ) ) {

			if ( !file_exists( Configuration::projectDir() . $this->config['dump'] ) ) {
				throw new ModuleConfigException( __CLASS__, "\nFile with dump doesn't exist.
                    Please, check path for sql file: " . $this->config['dump'] );
			}
			$sql       = file_get_contents( Configuration::projectDir() . $this->config['dump'] );
			$sql       = preg_replace( '%/\*(?!!\d+)(?:(?!\*/).)*\*/%s', "", $sql );
			$this->sql = explode( "\n", $sql );
		}

		try {
			$this->driver = Driver::create( $this->config['dsn'], $this->config['user'], $this->config['password'] );
		} catch ( \PDOException $e ) {
			throw new ModuleConfigException( __CLASS__, $e->getMessage() . ' while creating PDO connection' );
		}

		$this->dbh = $this->driver->getDbh();

		// starting with loading dump
		if ( $this->config['populate'] ) {
			$this->cleanup();
			$this->loadDump();
			$this->populated = true;
		}
		$this->tablePrefix = $this->config['tablePrefix'];
	}

	/**
	 * Inserts a user and appropriate meta in the database.
	 *
	 * @param  string $user_login The user login slug
	 * @param  string $role       The user role slug, e.g. "administrator"; defaults to "subscriber".
	 * @param  array  $userData   An associative array of column names and values overridind defaults in the "users"
	 *                            and "usermeta" table.
	 *
	 * @return void
	 */
	public function haveUserInDatabase( $user_login, $role = 'subscriber', array $userData = array() ) {
		// get the user
		$userTableData = User::generateUserTableDataFrom( $user_login, $userData );
		$this->debugSection( 'Generated users table data', json_encode( $userTableData ) );
		$this->haveInDatabase( $this->getUsersTableName(), $userTableData );

		$userId = $this->grabUserIdFromDatabase( $user_login );

		$this->haveUserCapabilitiesInDatabase( $userId, $role );
		$this->haveUserLevelsInDatabase( $userId, $role );
	}

	/**
	 * Returns the users table name, e.g. `wp_users`.
	 *
	 * @return string
	 */
	protected function getUsersTableName() {
		$usersTableName = $this->grabPrefixedTableNameFor( 'users' );

		return $usersTableName;
	}

	/**
	 * Returns a prefixed table name for the current blog.
	 *
	 * If the table is not one to be prefixed (e.g. `users`) then the proper table name will be returned.
	 *
	 * @param  string $tableName The table name, e.g. `options`.
	 *
	 * @return string            The prefixed table name, e.g. `wp_options` or `wp_2_options`.
	 */
	public function grabPrefixedTableNameFor( $tableName = '' ) {
		$idFrag = '';
		if ( !in_array( $tableName, $this->uniqueTables ) ) {
			$idFrag = empty( $this->blogId ) ? '' : "{$this->blogId}_";
		}

		$tableName = $this->config['tablePrefix'] . $idFrag . $tableName;

		return $tableName;
	}

	/**
	 * Gets the a user ID from the database using the user login.
	 *
	 * @param string $userLogin
	 *
	 * @return int The user ID
	 */
	public function grabUserIdFromDatabase( $userLogin ) {
		return $this->grabFromDatabase( 'wp_users', 'ID', [ 'user_login' => $userLogin ] );
	}

	/**
	 * Sets a user capabilities.
	 *
	 * @param int          $userId
	 * @param string|array $role Either a role string (e.g. `administrator`) or an associative array of blog IDs/roles
	 *                           for a multisite installation; e.g. `[1 => 'administrator`, 2 => 'subscriber']`.
	 *
	 * @return array An array of inserted `meta_id`.
	 */
	public function haveUserCapabilitiesInDatabase( $userId, $role ) {
		if ( !is_array( $role ) ) {
			$meta_key   = $this->grabPrefixedTableNameFor() . 'capabilities';
			$meta_value = serialize( [ $role => 1 ] );
			return $this->haveUserMetaInDatabase( $userId, $meta_key, $meta_value );
		}
		$ids = [ ];
		foreach ( $role as $blogId => $_role ) {
			$blogIdAndPrefix = $blogId == 0 ? '' : $blogId . '_';
			$meta_key        = $this->grabPrefixedTableNameFor() . $blogIdAndPrefix . 'capabilities';
			$meta_value      = serialize( [ $_role => 1 ] );
			$ids[]           = array_merge( $ids, $this->haveUserMetaInDatabase( $userId, $meta_key, $meta_value ) );
		}

		return $ids;
	}

	/**
	 * Sets a user meta.
	 *
	 * @param int    $userId
	 * @param string $meta_key
	 * @param mixed  $meta_value Either a single value or an array of values; objects will be serialized while array of
	 *                           values will trigger the insertion of multiple rows.
	 *
	 * @return array An array of inserted `user_id`.
	 */
	public function haveUserMetaInDatabase( $userId, $meta_key, $meta_value ) {
		$ids         = [ ];
		$meta_values = is_array( $meta_value ) ? $meta_value : [ $meta_value ];
		foreach ( $meta_values as $meta_value ) {
			$data  = [ 'user_id' => $userId, 'meta_key' => $meta_key, 'meta_value' => $this->maybeSerialize( $meta_value ) ];
			$ids[] = $this->haveInDatabase( $this->grabUsermetaTableName(), $data );
		}

		return $ids;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function maybeSerialize( $value ) {
		$metaValue = ( is_array( $value ) || is_object( $value ) ) ? serialize( $value ) : $value;

		return $metaValue;
	}

	/**
	 * Returns the prefixed `usermeta` table name, e.g. `wp_usermeta`.
	 *
	 * @return string
	 */
	public function grabUsermetaTableName() {
		$usermetaTable = $this->grabPrefixedTableNameFor( 'usermeta' );

		return $usermetaTable;
	}

	/**
	 * Sets the user level in the database for a user.
	 *
	 * @param int          $userId
	 * @param string|array $role Either a role string (e.g. `administrator`) or an array of blog IDs/roles for a
	 *                           multisite installation.
	 *
	 * @return array An array of inserted `meta_id`.
	 */
	public function haveUserLevelsInDatabase( $userId, $role ) {
		if ( !is_array( $role ) ) {
			$meta_key   = $this->grabPrefixedTableNameFor() . 'user_level';
			$meta_value = User\Roles::getLevelForRole( $role );
			return $this->haveUserMetaInDatabase( $userId, $meta_key, $meta_value );
		}
		$ids = [ ];
		foreach ( $role as $blogId => $_role ) {
			$blogIdAndPrefix = $blogId == 0 ? '' : $blogId . '_';
			$meta_key        = $this->grabPrefixedTableNameFor() . $blogIdAndPrefix . 'user_level';
			$meta_value      = User\Roles::getLevelForRole( $_role );
			$ids[]           = $this->haveUserMetaInDatabase( $userId, $meta_key, $meta_value );
		}

		return $ids;
	}

	/**
	 * Checks that an option is not in the database for the current blog.
	 *
	 * If the value is an object or an array then the serialized option will be checked for.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontSeeOptionInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'options' );
		if ( !empty( $criteria['option_value'] ) ) {
			$criteria['option_value'] = $this->maybeSerialize( $criteria['option_value'] );
		}
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks for a post meta value in the database for the current blog.
	 *
	 * If the `meta_value` is an object or an array then the serialized value will be checked for.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seePostMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'postmeta' );
		if ( !empty( $criteria['meta_value'] ) ) {
			$criteria['meta_value'] = $this->maybeSerialize( $criteria['meta_value'] );
		}
		$this->seeInDatabase( $tableName, $criteria );
	}

	/**
	 * Inserts a link in the database.
	 *
	 * Will insert in the "links" table.
	 *
	 * @param  int   $link_id The link id to insert.
	 * @param  array $data    The data to insert.
	 *
	 * @return void
	 */
	public function haveLinkInDatabase( $link_id, array $data = array() ) {
		if ( !is_int( $link_id ) ) {
			throw new \BadMethodCallException( 'Link id must be an int' );
		}
		$tableName = $this->grabPrefixedTableNameFor( 'links' );
		$data      = array_merge( $data, array( 'link_id' => $link_id ) );
		$this->haveInDatabase( $tableName, $data );
	}

	/**
	 * Checks for a link in the database.
	 *
	 * Will look up the "links" table.
	 *
	 * @param  array $criteria
	 *
	 * @return void
	 */
	public function seeLinkInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'links' );
		$this->seeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks for a link is not in the database.
	 *
	 * Will look up the "links" table.
	 *
	 * @param  array $criteria
	 *
	 * @return void
	 */
	public function dontSeeLinkInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'links' );
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a post meta value is not there.
	 *
	 * If the meta value is an object or an array then the serialized version will be checked for.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeePostMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'postmeta' );
		if ( !empty( $criteria['meta_value'] ) ) {
			$criteria['meta_value'] = $this->maybeSerialize( $criteria['meta_value'] );
		}
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a post to term relation exists in the database.
	 *
	 * Will look up the "term_relationships" table.
	 *
	 * @param  int     $post_id    The post ID.
	 * @param  int     $term_id    The term ID.
	 * @param  integer $term_order The order the term applies to the post, defaults to 0.
	 *
	 * @return void
	 */
	public function seePostWithTermInDatabase( $post_id, $term_id, $term_order = 0 ) {
		$tableName = $this->grabPrefixedTableNameFor( 'term_relationships' );
		$this->dontSeeInDatabase( $tableName, array( 'object_id' => $post_id, 'term_id' => $term_id, 'term_order' => $term_order ) );
	}

	/**
	 * Checks that a user is in the database.
	 *
	 * Will look up the "users" table.
	 *
	 * @param  array $criteria
	 *
	 * @return void
	 */
	public function seeUserInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'users' );
		$this->seeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a user is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeeUserInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'users' );
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Inserts a page in the database.
	 *
	 * @param array $overrides An array of values to override the default ones.
	 */
	public function havePageInDatabase( array $overrides = [ ] ) {
		$overrides = [ 'post_type' => 'page' ];
		return $this->havePostInDatabase( $overrides );
	}

	/**
	 * Inserts a post in the database.
	 *
	 * @param  array $data An associative array of post data to override default and random generated values.
	 */
	public function havePostInDatabase( array $data = [ ] ) {
		$postTableName = $this->grabPostsTableName();
		$idColumn      = 'ID';
		$id            = $this->grabLatestEntryByFromDatabase( $postTableName, $idColumn ) + 1;
		$post          = Post::makePost( $id, $this->config['url'], $data );
		$hasMeta       = !empty( $data['meta'] );
		if ( $hasMeta ) {
			$meta = $data['meta'];
			unset( $post['meta'] );
		}

		$postId = $this->haveInDatabase( $postTableName, $post );

		if ( $hasMeta ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				$this->havePostmetaInDatabase( $postId, $meta_key, $meta_value );
			}
		}

		return $postId;
	}

	/**
	 * Gets the posts table name.
	 *
	 * @return string The prefixed table name, e.g. `wp_posts`
	 */
	public function grabPostsTableName() {
		return $this->grabPrefixedTableNameFor( 'posts' );
	}

	/**
	 * Returns the id value of the last table entry.
	 *
	 * @param string $tableName
	 * @param string $idColumn
	 *
	 * @return mixed
	 */
	public function grabLatestEntryByFromDatabase( $tableName, $idColumn = 'ID' ) {
		$dbh = $this->driver->getDbh();
		$sth = $dbh->prepare( "SELECT {$idColumn} FROM {$tableName} ORDER BY {$idColumn} DESC LIMIT 1" );
		$this->debugSection( 'Query', $sth->queryString );
		$sth->execute();

		return $sth->fetchColumn();
	}

	/**
	 * Adds one or more meta key and value couples in the database for a post.
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 * @param mixed  $meta_value The value to insert in the database. Objects will be serialized and arrays will be added
	 *                           into distinct multiple rows.
	 *
	 * @return int|array Either the single `meta_id` of the inserted row or an array of inserted `meta_id`.
	 */
	public function havePostmetaInDatabase( $post_id, $meta_key, $meta_value ) {
		if ( !is_int( $post_id ) ) {
			throw new \BadMethodCallException( 'Post id must be an int', 1 );
		}
		if ( !is_string( $meta_key ) ) {
			throw new \BadMethodCallException( 'Meta key must be an string', 3 );
		}
		$tableName   = $this->grabPostmetaTableName();
		$meta_values = is_array( $meta_value ) ? $meta_value : [ $meta_value ];
		$meta_ids    = [ ];
		foreach ( $meta_values as $meta_value ) {
			$meta_ids[] = $this->haveInDatabase( $tableName, array( 'post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => $this->maybeSerialize( $meta_value ) ) );
		}
	}

	/**
	 * Returns the prefixed post meta table name.
	 *
	 * @return string The prefixed `postmeta` table name, e.g. `wp_postmeta`.
	 */
	public function grabPostmetaTableName() {
		return $this->grabPrefixedTableNameFor( 'postmeta' );
	}

	/**
	 * Checks for a page in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seePageInDatabase( array$criteria ) {
		$criteria['post_type'] = 'page';
		$this->seePostInDatabase( $criteria );
	}

	/**
	 * Checks for a post in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seePostInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'posts' );
		$this->seeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a page is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeePageInDatabase( array $criteria ) {
		$criteria['post_type'] = 'page';
		$this->dontSeePostInDatabase( $criteria );
	}

	/**
	 * Checks that a post is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeePostInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'posts' );
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Inserts a link to term relationship in the database.
	 *
	 * If "checkExistence" then will make some checks for missing term and/or link.
	 *
	 * @param  int     $link_id    The link ID.
	 * @param  int     $term_id    The term ID.
	 * @param  integer $term_order An optional term order value, will default to 0.
	 *
	 * @return void
	 */
	public function haveLinkWithTermInDatabase( $link_id, $term_id, $term_order = 0 ) {
		if ( !is_int( $link_id ) or !is_int( $term_id ) or !is_int( $term_order ) ) {
			throw new \BadMethodCallException( "Link ID, term ID and term order must be strings", 1 );
		}
		$this->maybeCheckTermExistsInDatabase( $term_id );
		// add the relationship in the database
		$tableName = $this->grabPrefixedTableNameFor( 'term_relationships' );
		$this->haveInDatabase( $tableName, array( 'object_id' => $link_id, 'term_taxonomy_id' => $term_id, 'term_order' => $term_order ) );
	}

	/**
	 * Conditionally checks that a term exists in the database.
	 *
	 * Will look up the "terms" table, will throw if not found.
	 *
	 * @param  int $term_id The term ID.
	 *
	 * @return void
	 */
	protected function maybeCheckTermExistsInDatabase( $term_id ) {
		if ( !isset( $this->config['checkExistence'] ) or false == $this->config['checkExistence'] ) {
			return;
		}
		$tableName = $this->grabPrefixedTableNameFor( 'terms' );
		if ( !$this->grabFromDatabase( $tableName, 'term_id', array( 'term_id' => $term_id ) ) ) {
			throw new \RuntimeException( "A term with an id of $term_id does not exist", 1 );
		}
	}

	/**
	 * Inserts a comment in the database.
	 *
	 * @param  int   $comment_post_ID The id of the post the comment refers to.
	 * @param  array $data            The comment data overriding default and random generated values.
	 *
	 * @return void
	 */
	public function haveCommentInDatabase( $comment_post_ID, array $data = array() ) {
		if ( !is_int( $comment_post_ID ) ) {
			throw new \BadMethodCallException( 'Comment id and post id must be int', 1 );
		}
		$comment   = Comment::makeComment( $comment_post_ID, $data );
		$tableName = $this->grabPrefixedTableNameFor( 'comments' );
		$this->haveInDatabase( $tableName, $comment );
	}

	/**
	 * Checks for a comment in the database.
	 *
	 * Will look up the "comments" table.
	 *
	 * @param  array $criteria
	 *
	 * @return void
	 */
	public function seeCommentInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'comments' );
		$this->seeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a comment is not in the database.
	 *
	 * Will look up the "comments" table.
	 *
	 * @param  array $criteria
	 *
	 * @return void
	 */
	public function dontSeeCommentInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'comments' );
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a comment meta value is in the database.
	 *
	 * Will look up the "commentmeta" table.
	 *
	 * @param  array $criteria
	 *
	 * @return void
	 */
	public function seeCommentMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'commentmeta' );
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a comment meta value is not in the database.
	 *
	 * Will look up the "commentmeta" table.
	 *
	 * @param  array $criteria
	 *
	 * @return void
	 */
	public function dontSeeCommentMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'commentmeta' );
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Inserts a post to term relationship in the database.
	 *
	 * Will conditionally check for post and term existence if "checkExistence" is set to true.
	 *
	 * @param  int     $post_id    The post ID.
	 * @param  int     $term_id    The term ID.
	 * @param  integer $term_order The optional term order.
	 *
	 * @return void
	 */
	public function havePostWithTermInDatabase( $post_id, $term_id, $term_order = 0 ) {
		if ( !is_int( $post_id ) or !is_int( $term_id ) or !is_int( $term_order ) ) {
			throw new \BadMethodCallException( "Post ID, term ID and term order must be strings", 1 );
		}
		$this->maybeCheckPostExistsInDatabase( $post_id );
		$this->maybeCheckTermExistsInDatabase( $term_id );
		// add the relationship in the database
		$tableName = $this->grabPrefixedTableNameFor( 'term_relationships' );
		$this->haveInDatabase( $tableName, array( 'object_id' => $post_id, 'term_taxonomy_id' => $term_id, 'term_order' => $term_order ) );
	}

	/**
	 * Conditionally checks that a post exists in database, will throw if not existent.
	 *
	 * @param  int $post_id The post ID.
	 *
	 * @return void
	 */
	protected function maybeCheckPostExistsInDatabase( $post_id ) {
		if ( !isset( $this->config['checkExistence'] ) or false == $this->config['checkExistence'] ) {
			return;
		}
		$tableName = $this->grabPrefixedTableNameFor( 'posts' );
		if ( !$this->grabFromDatabase( $tableName, 'ID', array( 'ID' => $post_id ) ) ) {
			throw new \RuntimeException( "A post with an id of $post_id does not exist", 1 );
		}
	}

	/**
	 * Inserts a commment meta value in the database.
	 *
	 * @param  int    $comment_id The comment ID.
	 * @param  string $meta_key
	 * @param         string      /int $meta_value
	 * @param  int    $meta_id    The optinal meta ID.
	 *
	 * @return void
	 */
	public function haveCommentMetaInDatabase( $comment_id, $meta_key, $meta_value, $meta_id = null ) {
		if ( !is_int( $comment_id ) ) {
			throw new \BadMethodCallException( 'Comment id must be an int', 1 );
		}
		if ( !is_null( $meta_id ) and !is_int( $meta_key ) ) {
			throw new \BadMethodCallException( 'Meta id must be either null or an int', 2 );
		}
		if ( !is_string( $meta_key ) ) {
			throw new \BadMethodCallException( 'Meta key must be an string', 3 );
		}
		if ( !is_string( $meta_value ) ) {
			throw new \BadMethodCallException( 'Meta value must be an string', 4 );
		}
		$this->maybeCheckCommentExistsInDatabase( $comment_id );
		$tableName = $this->grabPrefixedTableNameFor( 'commmentmeta' );
		$this->haveInDatabase( $tableName, array( 'meta_id' => $meta_id, 'comment_id' => $comment_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ) );
	}

	/**
	 * Conditionally checks that a comment exists in database, will throw if not existent.
	 *
	 * @param $comment_id
	 *
	 */
	protected function maybeCheckCommentExistsInDatabase( $comment_id ) {
		if ( !isset( $this->config['checkExistence'] ) or false == $this->config['checkExistence'] ) {
			return;
		}
		$tableName = $this->grabPrefixedTableNameFor( 'comments' );
		if ( !$this->grabFromDatabase( $tableName, 'comment_ID', array( 'commment_ID' => $comment_id ) ) ) {
			throw new \RuntimeException( "A comment with an id of $comment_id does not exist", 1 );
		}
	}

	/**
	 * Inserts a term in the database.
	 *
	 * @param  string $name      The term name, e.g. "Fuzzy".
	 * @param string  $taxonomy  The term taxonomy
	 * @param array   $overrides An array of values to override the default ones.
	 *
	 */
	public function haveTermInDatabase( $name, $taxonomy, array $overrides = [ ] ) {
		$termDefaults     = [ 'slug' => ( new Slugifier() )->slugify( $name ), 'term_group' => 0 ];
		$termData         = array_merge( $termDefaults, array_intersect_key( $overrides, $termDefaults ) );
		$termData['name'] = $name;
		$term_id          = $this->haveInDatabase( $this->grabTermsTableName(), $termData );

		$termTaxonomyDefaults         = [ 'description' => '', 'parent' => 0, 'count' => 0 ];
		$termTaxonomyData             = array_merge( $termTaxonomyDefaults, array_intersect_key( $overrides, $termTaxonomyDefaults ) );
		$termTaxonomyData['taxonomy'] = $taxonomy;
		$termTaxonomyData['term_id']  = $term_id;
		$term_taxonomy_id             = $this->haveInDatabase( $this->grabTermTaxonomyTableName(), $termTaxonomyData );

		return [ $term_id, $term_taxonomy_id ];
	}

	/**
	 * Gets the prefixed terms table name, e.g. `wp_terms`.
	 *
	 * @return string
	 */
	public function grabTermsTableName() {
		return $this->grabPrefixedTableNameFor( 'terms' );

	}

	/**
	 * Gets the prefixed term and taxonomy table name, e.g. `wp_term_taxonomy`.
	 *
	 * @return string
	 */
	public function grabTermTaxonomyTableName() {
		return $this->grabPrefixedTableNameFor( 'term_taxonomy' );
	}

	/**
	 * Checks for a user meta value in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function seeUserMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'usermeta' );
		$this->seeInDatabase( $tableName, $criteria );
	}

	/**
	 * Check that a user meta value is not in the database.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontSeeUserMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'usermeta' );
		$this->dontSeeInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the commentmeta table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveCommentMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'commentmeta' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the comment table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveCommentInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'comments' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the links table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveLinkInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'links' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the postmeta table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHavePostMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'postmeta' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the posts table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHavePostInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'posts' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the term_relationships table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveTermRelationshipInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'term_relationships' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the term_taxonomy table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveTermTaxonomyInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'term_taxonomy' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes an entry from the usermeta table.
	 *
	 * @param  array $criteria An array of search criteria.
	 */
	public function dontHaveUserMetaInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'usermeta' );
		$this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Removes a user from the database.
	 *
	 * @param int|string $userIdOrLogin
	 */
	public function dontHaveUserInDatabase( $userIdOrLogin ) {
		$userId = is_numeric( $userIdOrLogin ) ? intval( $userIdOrLogin ) : $this->grabUserIdFromDatabase( $userIdOrLogin );
		$this->dontHaveInDatabase( $this->grabPrefixedTableNameFor( 'users' ), [ 'ID' => $userId ] );
		$this->dontHaveInDatabase( $this->grabPrefixedTableNameFor( 'usermeta' ), [ 'user_id' => $userId ] );
	}

	/**
	 * Gets a user meta from the database.
	 *
	 * @param int    $userId
	 * @param string $meta_key
	 * @return array An associative array of meta key/values.
	 */
	public function grabUserMetaFromDatabase( $userId, $meta_key ) {
		$table = $this->grabPrefixedTableNameFor( 'usermeta' );
		$meta  = $this->grabAllFromDatabase( $table, 'meta_value', [ 'user_id' => $userId, 'meta_key' => $meta_key ] );
		if ( empty( $meta ) ) {
			return [ ];
		}

		return array_map( function ( $val ) {
			return $val['meta_value'];
		}, $meta );
	}

	/**
	 * Returns all entries matching a criteria from the database.
	 *
	 * @param string $table
	 * @param string $column
	 * @param array  $criteria
	 * @return array An array of results.
	 * @throws \Exception
	 */
	public function grabAllFromDatabase( $table, $column, $criteria ) {
		$query = $this->driver->select( $column, $table, $criteria );

		$sth = $this->driver->executeQuery( $query, array_values( $criteria ) );

		return $sth->fetchAll( PDO::FETCH_ASSOC );
	}

	/**
	 * Inserts a transient in the database.
	 *
	 * If the value is an array or an object then the value will be serialized.
	 *
	 * @param string $transient
	 * @param mixed  $value
	 * @return The inserted option `option_id`.
	 */
	public function haveTransientInDatabase( $transient, $value ) {
		return $this->haveOptionInDatabase( '_transient_' . $transient, $value );
	}

	/**
	 * Inserts an option in the database.
	 *
	 * If the option value is an object or an array then the value will be serialized.
	 *
	 * @param  string $option_name
	 * @param  mixed  $option_value
	 * @return The inserted `option_id`
	 */
	public function haveOptionInDatabase( $option_name, $option_value ) {
		$table = $this->grabPrefixedTableNameFor( 'options' );
		$this->dontHaveInDatabase( $table, [ 'option_name' => $option_name ] );
		$option_value = $this->maybeSerialize( $option_value );

		return $this->haveInDatabase( $table, array( 'option_name' => $option_name, 'option_value' => $option_value, 'autoload' => 'yes' ) );
	}

	/**
	 * Removes a transient from the database.
	 *
	 * @param $transient
	 * @return The removed option `option_id`.
	 */
	public function dontHaveTransientInDatabase( $transient ) {
		return $this->dontHaveOptionInDatabase( '_transient_' . $transient );
	}

	/**
	 * Removes an entry from the options table.
	 *
	 * @param      $key
	 * @param null $value
	 * @return The removed option `option_id`.
	 */
	public function dontHaveOptionInDatabase( $key, $value = null ) {
		$tableName               = $this->grabPrefixedTableNameFor( 'options' );
		$criteria['option_name'] = $key;
		if ( !empty( $value ) ) {
			$criteria['option_value'] = $value;
		}
		return $this->dontHaveInDatabase( $tableName, $criteria );
	}

	/**
	 * Inserts a site option in the database.
	 *
	 * If the value is an array or an object then the value will be serialized.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return The inserted option `option_id`.
	 */
	public function haveSiteOptionInDatabase( $key, $value ) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$option_id = $this->haveOptionInDatabase( '_site_option_' . $key, $value );
		$this->useBlog( $currentBlogId );

		return $option_id;
	}

	/**
	 * Sets the current blog to the main one (`blog_id` 1).
	 */
	public function useMainBlog() {
		$this->useBlog( 0 );
	}

	/**
	 * Sets the blog to be used.
	 *
	 * @param int $id
	 */
	public function useBlog( $id = 0 ) {
		if ( !( is_numeric( $id ) && intval( $id ) === $id && intval( $id ) >= 0 ) ) {
			throw new \InvalidArgumentException( 'Id must be an integer greater than or equal to 0' );
		}
		$this->blogId = intval( $id );
	}

	/**
	 * Removes a site option from the database.
	 *
	 * @param      $key
	 * @param null $value
	 */
	public function dontHaveSiteOptionInDatabase( $key, $value = null ) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$this->dontHaveOptionInDatabase( '_site_option_' . $key, $value );
		$this->useBlog( $currentBlogId );
	}

	/**
	 * Inserts a site transient in the database.
	 *
	 * If the value is an array or an object then the value will be serialized.
	 *
	 * @param $key
	 * @param $value
	 */
	public function haveSiteTransientInDatabase( $key, $value ) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$option_id = $this->haveOptionInDatabase( '_site_transient_' . $key, $value );
		$this->useBlog( $currentBlogId );

		return $option_id;
	}

	/**
	 * Removes a site transient from the database.
	 *
	 * @param string $key
	 */
	public function dontHaveSiteTransientInDatabase( $key ) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$this->dontHaveOptionInDatabase( '_site_transient_' . $key );
		$this->useBlog( $currentBlogId );
	}

	/**
	 * Gets a site option from the database.
	 *
	 * @param string $key
	 * @return mixed|string
	 */
	public function grabSiteOptionFromDatabase( $key ) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$value = $this->grabOptionFromDatabase( '_site_option_' . $key );
		$this->useBlog( $currentBlogId );

		return $value;
	}

	/**
	 * Gets an option from the database.
	 *
	 * @param string $option_name
	 * @return mixed|string
	 */
	public function grabOptionFromDatabase( $option_name ) {
		$table        = $this->grabPrefixedTableNameFor( 'options' );
		$option_value = $this->grabFromDatabase( $table, 'option_value', [ 'option_name' => $option_name ] );

		return empty( $option_value ) ? '' : $this->maybeUnserialize( $option_value );
	}

	private function maybeUnserialize( $value ) {
		$unserialized = @unserialize( $value );

		return false === $unserialized ? $value : $unserialized;
	}

	/**
	 * Gets a site transient from the database.
	 *
	 * @param string $key
	 * @return mixed|string
	 */
	public function grabSiteTransientFromDatabase( $key ) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$value = $this->grabOptionFromDatabase( '_site_transient_' . $key );
		$this->useBlog( $currentBlogId );

		return $value;
	}

	/**
	 * Checks that a site option is in the database.
	 *
	 * @param string     $key
	 * @param mixed|null $value
	 */
	public function seeSiteSiteTransientInDatabase( $key, $value = null ) {
		$currentBlogId = $this->blogId;
		$criteria      = [ 'option_name' => '_site_transient_' . $key ];
		if ( $value ) {
			$criteria['option_value'] = $value;
		}
		$this->seeOptionInDatabase( $criteria );
		$this->useBlog( $currentBlogId );
	}

	/**
	 * Checks if an option is in the database for the current blog.
	 *
	 * If checking for an array or an object then the serialized version will be checked for.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function seeOptionInDatabase( array $criteria ) {
		$tableName = $this->grabPrefixedTableNameFor( 'options' );
		if ( !empty( $criteria['option_value'] ) ) {
			$criteria['option_value'] = $this->maybeSerialize( $criteria['option_value'] );
		}
		$this->seeInDatabase( $tableName, $criteria );
	}

	/**
	 * Checks that a site option is in the database.
	 *
	 * @param string     $key
	 * @param mixed|null $value
	 */
	public function seeSiteOptionInDatabase( $key, $value = null ) {
		$currentBlogId = $this->blogId;
		$this->useMainBlog();
		$criteria = [ 'option_name' => '_site_option_' . $key ];
		if ( $value ) {
			$criteria['option_value'] = $value;
		}
		$this->seeOptionInDatabase( $criteria );
		$this->useBlog( $currentBlogId );
	}

	/**
	 * Returns the current site url as specified in the module configuration.
	 *
	 * @return string The current site URL
	 */
	public function grabSiteUrl() {
		return $this->config['url'];
	}

	/**
	 * Inserts many posts in the database returning their IDs.
	 *
	 * @param       $count     The number of posts to insert.
	 * @param array $overrides {
	 *                         An array of values to override the defaults.
	 *                         The `{{n}}` placeholder can be used to have the post count inserted in its place;
	 *                         e.g. `Post Title - {{n}}` will be set to `Post Title - 0` for the first post,
	 *                         `Post Title - 1` for the second one and so on.
	 *                         The same applies to meta values as well.
	 *
	 * @type array  $meta      An associative array of meta key/values to be set for the post, shorthand for the `havePostmetaInDatabase` method.
	 *                    e.g. `['one' => 'foo', 'two' => 'bar']`; to have an array value inserted in a single row serialize it e.g.
	 *                    `['serialized_field` => serialize(['one','two','three'])]` otherwise a distinct row will be added for each entry.
	 *                    See `havePostmetaInDatabase` method.
	 * }
	 *
	 * @return array
	 */
	public function haveManyPostsInDatabase( $count, array $overrides = [ ] ) {
		if ( !is_int( $count ) ) {
			throw new \InvalidArgumentException( 'Count must be an integer value' );
		}
		$ids = [ ];
		for ( $i = 0; $i < $count; $i++ ) {
			$thisOverrides = $this->replaceNumbersInArray( $overrides, $i );
			$ids[]         = $this->havePostInDatabase( $thisOverrides );
		}

		return $ids;
	}

	protected function replaceNumbersInArray( $entry, $i ) {
		$out = [ ];
		foreach ( $entry as $key => $value ) {
			if ( is_array( $value ) ) {
				$out[$this->replaceNumbersInString( $key, $i )] = $this->replaceNumbersInArray( $value, $i );
			} else {
				$out[$this->replaceNumbersInString( $key, $i )] = $this->replaceNumbersInString( $value, $i );
			}
		}

		return $out;
	}

	/**
	 * @param $value
	 * @param $i
	 *
	 * @return mixed
	 */
	protected function replaceNumbersInString( $value, $i ) {
		return str_replace( '{{n}}', $i, $value );
	}

	/**
	 * Checks for a term in the database.
	 *
	 * Looks up the `terms` and `term_taxonomy` prefixed tables.
	 *
	 * @param array $criteria An array of criteria to search for the term, can be columns from the `terms` and the
	 *                        `term_taxonomy` tables.
	 */
	public function seeTermInDatabase( array $criteria ) {
		try {
			$termsCriteria        = array_intersect_key( $criteria, array_flip( $this->termKeys ) );
			$termTaxonomyCriteria = array_intersect_key( $criteria, array_flip( $this->termTaxonomyKeys ) );

			if ( !empty( $termsCriteria ) ) {
				// this one fails... go to...
				$this->seeInDatabase( $this->grabTermsTableName(), $termsCriteria );
			}
			if ( !empty( $termTaxonomyCriteria ) ) {
				$this->seeInDatabase( $this->grabTermTaxonomyTableName(), $termTaxonomyCriteria );
			}
		} catch ( PHPUnit_Framework_ExpectationFailedException $e ) {
			// ...this one
			if ( !empty( $termTaxonomyCriteria ) ) {
				$this->seeInDatabase( $this->grabTermTaxonomyTableName(), $termTaxonomyCriteria );
			}
		}
	}

	/**
	 * Removes a term from the database.
	 *
	 * @param array $criteria An array of search criteria.
	 */
	public function dontHaveTermInDatabase( array $criteria ) {
		$termRelationshipsKeys = [ 'term_taxonomy_id' ];

		$this->dontHaveInDatabase( $this->grabTermsTableName(), array_intersect_key( $criteria, array_flip( $this->termKeys ) ) );
		$this->dontHaveInDatabase( $this->grabTermTaxonomyTableName(), array_intersect_key( $criteria, array_flip( $this->termTaxonomyKeys ) ) );
		$this->dontHaveInDatabase( $this->grabTermRelationshipsTableName(), array_intersect_key( $criteria, array_flip( $termRelationshipsKeys ) ) );
	}

	/**
	 * Gets the prefixed term relationships table name, e.g. `wp_term_relationships`.
	 *
	 * @return string
	 */
	public function grabTermRelationshipsTableName() {
		return $this->grabPrefixedTableNameFor( 'term_relationships' );
	}

	/**
	 * Makes sure a term is not in the database.
	 *
	 * Looks up both the `terms` table and the `term_taxonomy` tables.
	 *
	 * @param array $criteria An array of criteria to search for the term, can be columns from the `terms` and the
	 *                        `term_taxonomy` tables.
	 */
	public function dontSeeTermInDatabase( array $criteria ) {
		try {
			$termsCriteria        = array_intersect_key( $criteria, array_flip( $this->termKeys ) );
			$termTaxonomyCriteria = array_intersect_key( $criteria, array_flip( $this->termTaxonomyKeys ) );

			if ( !empty( $termsCriteria ) ) {
				// this one fails... go to...
				$this->dontSeeInDatabase( $this->grabTermsTableName(), $termsCriteria );
			}
			if ( !empty( $termTaxonomyCriteria ) ) {
				$this->dontSeeInDatabase( $this->grabTermTaxonomyTableName(), $termTaxonomyCriteria );
			}
		} catch ( PHPUnit_Framework_ExpectationFailedException $e ) {
			// ...this one
			if ( !empty( $termTaxonomyCriteria ) ) {
				$this->dontSeeInDatabase( $this->grabTermTaxonomyTableName(), $termTaxonomyCriteria );
			}
		}
	}

	/**
	 * Conditionally checks for a user in the database.
	 *
	 * Will look up the "users" table, will throw if not found.
	 *
	 * @param  int $user_id The user ID.
	 */
	protected function maybeCheckUserExistsInDatabase( $user_id ) {
		if ( !isset( $this->config['checkExistence'] ) or false == $this->config['checkExistence'] ) {
			return;
		}
		$tableName = $this->grabPrefixedTableNameFor( 'users' );
		if ( !$this->grabFromDatabase( $tableName, 'ID', array( 'ID' => $user_id ) ) ) {
			throw new \RuntimeException( "A user with an id of $user_id does not exist", 1 );
		}
	}

	/**
	 * Conditionally check for a link in the database.
	 *
	 * Will look up the "links" table, will throw if not found.
	 *
	 * @param  int $link_id The link ID.
	 *
	 * @return bool True if the link exists, false otherwise.
	 */
	protected function maybeCheckLinkExistsInDatabase( $link_id ) {
		if ( !isset( $this->config['checkExistence'] ) or false == $this->config['checkExistence'] ) {
			return;
		}
		$tableName = $this->grabPrefixedTableNameFor( 'links' );
		if ( !$this->grabFromDatabase( $tableName, 'link_id', array( 'link_id' => $link_id ) ) ) {
			throw new \RuntimeException( "A link with an id of $link_id does not exist", 1 );
		}
	}
}