<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUsRequest;
use App\Http\Resources\ContactUsResource;
use App\Models\ContactUs;
use Illuminate\Http\JsonResponse;

class ContactUsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/contact-us",
     *     tags={"Contact Us"},
     *     operationId="submitContactUs",
     *     summary="Submit a contact us form",
     *     description="Submit a contact us form with name, email, affiliation, subject, and message",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "subject", "message"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="affiliation", type="string", nullable=true, example="Acme Corp"),
     *             @OA\Property(property="subject", type="string", example="General Inquiry"),
     *             @OA\Property(property="message", type="string", example="I would like to know more about your services.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact us submission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/ContactUs")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(ContactUsRequest $request): JsonResponse
    {
        $contactUs = ContactUs::create($request->validated());

        return (new ContactUsResource($contactUs))
            ->response()
            ->setStatusCode(201);
    }
}
