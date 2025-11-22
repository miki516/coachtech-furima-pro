<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function トップページが表示できること()
    {
        $res = $this->get('/');

        $res->assertOk();          // ステータス200？
        $res->assertSee('商品一覧'); // ページに「商品一覧」が含まれてる？
    }
}
