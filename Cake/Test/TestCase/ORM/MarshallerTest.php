<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 3.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\ORM;

use Cake\ORM\Marshaller;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Marshaller test case
 */
class MarshallerTest extends TestCase {

	public $fixtures = ['core.article', 'core.user', 'core.comment'];

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$articles = TableRegistry::get('Articles');
		$articles->belongsTo('Users');
		$articles->hasMany('Comments');

		$comments = TableRegistry::get('Comments');
		$comments->belongsTo('Articles');
		$comments->belongsTo('Users');

		$this->articles = $articles;
		$this->comments = $comments;
	}

/**
 * Teardown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		TableRegistry::clear();
		unset($this->articles, $this->comments);
	}

/**
 * Test one() in a simple use.
 *
 * @return void
 */
	public function testOneSimple() {
		$data = [
			'title' => 'My title',
			'body' => 'My content',
			'author_id' => 1,
			'not_in_schema' => true
		];
		$marshall = new Marshaller($this->articles);
		$result = $marshall->one($data, []);

		$this->assertInstanceOf('Cake\ORM\Entity', $result);
		$this->assertEquals($data, $result->toArray());
		$this->assertTrue($result->dirty(), 'Should be a dirty entity.');
		$this->assertNull($result->isNew(), 'Should be detached');
	}

/**
 * test one() with a wrapping model name.
 *
 * @return void
 */
	public function testOneWithAdditionalName() {
		$data = [
			'Articles' => [
				'title' => 'My title',
				'body' => 'My content',
				'author_id' => 1,
				'not_in_schema' => true,
				'user' => [
					'username' => 'mark',
				]
			]
		];
		$marshall = new Marshaller($this->articles);
		$result = $marshall->one($data, ['Users']);

		$this->assertInstanceOf('Cake\ORM\Entity', $result);
		$this->assertTrue($result->dirty(), 'Should be a dirty entity.');
		$this->assertNull($result->isNew(), 'Should be detached');
		$this->assertEquals($data['Articles']['title'], $result->title);
		$this->assertEquals($data['Articles']['user']['username'], $result->user->username);
	}

/**
 * test one() with association data.
 *
 * @return void
 */
	public function testOneAssociationsSingle() {
		$data = [
			'title' => 'My title',
			'body' => 'My content',
			'author_id' => 1,
			'comments' => [
				['comment' => 'First post', 'user_id' => 2],
				['comment' => 'Second post', 'user_id' => 2],
			],
			'user' => [
				'username' => 'mark',
				'password' => 'secret'
			]
		];
		$marshall = new Marshaller($this->articles);
		$result = $marshall->one($data, ['Users']);

		$this->assertEquals($data['title'], $result->title);
		$this->assertEquals($data['body'], $result->body);
		$this->assertEquals($data['author_id'], $result->author_id);

		$this->assertInternalType('array', $result->comments);
		$this->assertEquals($data['comments'], $result->comments);

		$this->assertInstanceOf('Cake\ORM\Entity', $result->user);
		$this->assertEquals($data['user']['username'], $result->user->username);
		$this->assertEquals($data['user']['password'], $result->user->password);
	}

/**
 * test one() with association data.
 *
 * @return void
 */
	public function testOneAssociationsMany() {
		$data = [
			'title' => 'My title',
			'body' => 'My content',
			'author_id' => 1,
			'comments' => [
				['comment' => 'First post', 'user_id' => 2],
				['comment' => 'Second post', 'user_id' => 2],
			],
			'user' => [
				'username' => 'mark',
				'password' => 'secret'
			]
		];
		$marshall = new Marshaller($this->articles);
		$result = $marshall->one($data, ['Comments']);

		$this->assertEquals($data['title'], $result->title);
		$this->assertEquals($data['body'], $result->body);
		$this->assertEquals($data['author_id'], $result->author_id);

		$this->assertInternalType('array', $result->comments);
		$this->assertCount(2, $result->comments);
		$this->assertInstanceOf('Cake\ORM\Entity', $result->comments[0]);
		$this->assertInstanceOf('Cake\ORM\Entity', $result->comments[1]);
		$this->assertEquals($data['comments'][0]['comment'], $result->comments[0]->comment);

		$this->assertInternalType('array', $result->user);
		$this->assertEquals($data['user'], $result->user);
	}

/**
 * Test one() with deeper associations.
 *
 * @return void
 */
	public function testOneDeepAssociations() {
		$data = [
			'comment' => 'First post',
			'user_id' => 2,
			'article' => [
				'title' => 'Article title',
				'body' => 'Article body',
				'user' => [
					'username' => 'mark',
					'password' => 'secret'
				],
			]
		];
		$marshall = new Marshaller($this->comments);
		$result = $marshall->one($data, ['Articles' => ['associated' => ['Users']]]);

		$this->assertEquals(
			$data['article']['title'],
			$result->article->title
		);
		$this->assertEquals(
			$data['article']['user']['username'],
			$result->article->user->username
		);
	}

/**
 * Test many() with a simple set of data.
 *
 * @return void
 */
	public function testManySimple() {
		$data = [
			['comment' => 'First post', 'user_id' => 2],
			['comment' => 'Second post', 'user_id' => 2],
		];
		$marshall = new Marshaller($this->comments);
		$result = $marshall->many($data);

		$this->assertCount(2, $result);
		$this->assertInstanceOf('Cake\ORM\Entity', $result[0]);
		$this->assertInstanceOf('Cake\ORM\Entity', $result[1]);
		$this->assertEquals($data[0]['comment'], $result[0]->comment);
		$this->assertEquals($data[1]['comment'], $result[1]->comment);
	}

/**
 * test many() with nested associations.
 *
 * @return void
 */
	public function testManyAssociations() {
		$data = [
			[
				'comment' => 'First post',
				'user_id' => 2,
				'user' => [
					'username' => 'mark',
				],
			],
			[
				'comment' => 'Second post',
				'user_id' => 2,
				'user' => [
					'username' => 'jose',
				],
			],
		];
		$marshall = new Marshaller($this->comments);
		$result = $marshall->many($data, ['Users']);

		$this->assertCount(2, $result);
		$this->assertInstanceOf('Cake\ORM\Entity', $result[0]);
		$this->assertInstanceOf('Cake\ORM\Entity', $result[1]);
		$this->assertEquals(
			$data[0]['user']['username'],
			$result[0]->user->username
		);
		$this->assertEquals(
			$data[1]['user']['username'],
			$result[1]->user->username
		);
	}

}