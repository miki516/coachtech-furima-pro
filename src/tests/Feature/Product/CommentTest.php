<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Comment;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'postal_code'       => '123-4567',
            'address'           => '東京都テスト区1-2-3',
            'building'          => 'テストビル',
        ]);
    }

    /** ログイン済みのユーザーはコメントを送信でき、コメント数が増える */
    public function test_logged_in_user_can_post_comment_and_count_increases()
    {
        $user = $this->verifiedUser();
        $product = Product::factory()->create();

        $res = $this->actingAs($user)->post(route('comments.store'), [
            'product_id' => $product->id,
            'content' => 'テストコメント'
        ]);

        $res->assertStatus(302); // 成功でリダイレクトされる

        // DBに登録されたか
        $this->assertDatabaseHas('comments', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'content' => 'テストコメント',
        ]);

        // 詳細ページで増加表示を確認（「コメント (1)」）
        $page = $this->get(route('products.show', ['item_id' => $product->id]));
        $page->assertOk();
        $page->assertSee('コメント (1)');
        $page->assertSee('テストコメント');
    }

    /** ログイン前のユーザーはコメントを送信できない */
    public function test_guest_cannot_post_comment()
    {
        $this->withMiddleware();

        $product = Product::factory()->create();

        $res = $this->post(route('comments.store'), [
            'product_id' => $product->id,
            'content' => 'ゲストコメント',
        ]);

        $res->assertRedirect(route('login'));
        $this->assertDatabaseMissing('comments', [
            'product_id' => $product->id,
            'content' => 'ゲストコメント',
        ]);
    }

    /** コメント未入力ならバリデーションエラー */
    public function test_comment_content_is_required()
    {
        $user = $this->verifiedUser();
        $product = Product::factory()->create(['seller_id' => $user->id]);

        $res = $this->from(route('products.show', ['item_id' => $product->id]))
            ->actingAs($user)
            ->post(route('comments.store'), [
                'product_id' => $product->id,
                'content' => '', // 空
            ]);

        $res->assertRedirect(route('products.show', ['item_id' => $product->id]));
        $res->assertSessionHasErrors('content');
        $this->assertDatabaseCount('comments', 0);
    }

    /** コメントが255字超ならバリデーションエラー */
    public function test_comment_content_must_be_255_chars_or_less()
    {
        $user = $this->verifiedUser();
        $product = Product::factory()->create();

        $tooLong = str_repeat('a', 256); // 256文字

        $res = $this->from(route('products.show', ['item_id' => $product->id]))
            ->actingAs($user)
            ->post(route('comments.store'), [
                'product_id' => $product->id,
                'content' => $tooLong,
            ]);

        $res->assertRedirect(route('products.show', ['item_id' => $product->id]));
        $res->assertSessionHasErrors('content');
        $this->assertDatabaseCount('comments', 0);
    }
}
