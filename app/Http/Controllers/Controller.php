<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="E-learning app Api docs",
 *     version="1.0.0",
 *     description="API documentation for my E-learning platform"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT Bearer token in format: Bearer {token}"
 * )
 */
abstract class Controller
{
    //
}
