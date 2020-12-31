<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse as TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $category;
    private $sendData;

    protected function setUp(): void
    {
        parent::setup();
        $this->category = factory(Category::class)->create();
        $this->sendData = [
            'name' => 'test',
            'description' => 'test_description',
            'is_active' => false
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a',
        ];
        $this->assertInvalidationStoreAction($data, 'boolean');
        $this->assertInvalidationUpdateAction($data, 'boolean');
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $this
            ->assertInvalidationFields(
                $response,
                ['name'],
                'required',
            );
        $response->assertJsonMissingValidationErrors(['is_active']);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $this
            ->assertInvalidationFields(
                $response,
                ['name'],
                'max.string',
                ['max' => 255]
            );
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $this->assertInvalidationFields(
            $response,
            ['is_active'],
            'boolean'
        );
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $this->category->id]));
        $response->assertStatus(204);

        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    public function testStore()
    {
        $response = $this->assertStore($this->sendData, $this->sendData);
        $response->assertJsonStructure([
            'created_at',
            'updated_at',
            'deleted_at'
        ]);
        $this->assertStore(
            $this->sendData + ['is_active' => true],
            $this->sendData + ['is_active' => true]
        );
    }

    public function testUpdate()
    {
        $response = $this->assertUpdate($this->sendData, $this->sendData);
        $response->assertJsonStructure([
            'created_at',
            'updated_at',
        ]);
        $this->assertUpdate(
            $this->sendData + ['is_active' => true],
            $this->sendData + ['is_active' => true]
        );
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
