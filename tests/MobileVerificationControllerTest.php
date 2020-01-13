<?php

namespace Fouladgar\MobileVerifier\Tests;

use Fouladgar\MobileVerifier\Concerns\TokenBroker;
use Fouladgar\MobileVerifier\Tests\Models\VerifiableUser;
use Symfony\Component\HttpFoundation\Response;
use Mockery as m;

class MobileVerificationControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_verify_a_user()
    {
        $user = factory(VerifiableUser::class)->create();

        $this->actingAs($user);

        $tokenBroker = m::mock(TokenBroker::class);
        $tokenBroker->shouldReceive('verifyToken')->andReturn(true);

        $this->app->instance(TokenBroker::class, $tokenBroker);

        $response = $this->postJson(route('mobile.verify'), ['token' => '12345']);

        $response->assertStatus(Response::HTTP_OK);

        $response = $this->post(route('mobile.verify'), ['token' => '12345']);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    /** @test */
    public function it_failes_on_verifing_when_user_is_already_verified()
    {
        $user = factory(VerifiableUser::class)->state('verified')->create();

        $this->actingAs($user);

        $response = $this->postJson(route('mobile.verify'), ['token' => '12345']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->post(route('mobile.verify'), ['token' => '12345']);

        $response->assertStatus(Response::HTTP_FOUND);
    }

    /** @test */
    public function it_will_check_validation_for_token_verification()
    {
        $user = factory(VerifiableUser::class)->make();

        $this->actingAs($user);

        $response = $this->postJson(route('mobile.verify'));

        $response->assertJsonValidationErrors(['token'])
                 ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function it_fails_on_verifing_a_user()
    {
        $user = factory(VerifiableUser::class)->create();

        $this->actingAs($user);

        $response = $this->postJson(route('mobile.verify'), ['token' => '12345']);

        $response->assertStatus(Response::HTTP_NOT_ACCEPTABLE);
    }

    /**
     * @test
     */
    public function it_failes_on_resend_when_user_is_already_verified()
    {
        $user = factory(VerifiableUser::class)->state('verified')->create();

        $this->actingAs($user);

        $response = $this->postJson(route('mobile.resend'));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = $this->post(route('mobile.resend'));

        $response->assertViewHas('mobileVerificationError');
    }

    /**
     * @test
     */
    public function it_can_resend_a_token()
    {
        $user = factory(VerifiableUser::class)->create();

        $this->actingAs($user);

        $response = $this->postJson(route('mobile.resend'));

        $response->assertStatus(Response::HTTP_OK);

        $response = $this->post(route('mobile.resend'));

        $response->assertStatus(Response::HTTP_FOUND);
    }
}