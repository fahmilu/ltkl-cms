<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JoinFormSubmissionRequest;
use App\Http\Resources\JoinFormSubmissionResource;
use App\Mail\JoinFormSubmissionReceived;
use App\Models\JoinFormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inerba\DbConfig\DbConfig;
use Throwable;

class JoinFormSubmissionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/join-form-submissions",
     *     tags={"Join Form"},
     *     operationId="submitJoinForm",
     *     summary="Submit the join form",
     *     description="Stores a join form submission and emails it to the address in Website settings › Join Us. The participation pathway must be an active one from /api/participation-pathways.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "region", "participation_pathway_id", "message"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@organisasi.org"),
     *             @OA\Property(property="organization", type="string", nullable=true, example="Acme Corp"),
     *             @OA\Property(property="region", type="string", example="Siak, Riau"),
     *             @OA\Property(property="participation_pathway_id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Saya tertarik menjadi donatur.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Join form submission created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(JoinFormSubmissionRequest $request): JsonResponse
    {
        $submission = JoinFormSubmission::create($request->validated());

        $submission->load('participationPathway');

        $this->notifyRecipient($submission);

        return (new JoinFormSubmissionResource($submission))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Email the submission to the address set in Website settings › Join Us.
     *
     * The submission is already stored, so a missing address or a mail server
     * that is down is logged rather than failing the visitor's request.
     */
    private function notifyRecipient(JoinFormSubmission $submission): void
    {
        $recipient = DbConfig::get('website.join_us_email');

        if (blank($recipient)) {
            Log::warning('Join form submission stored without a notification: no recipient set in website.join_us_email.', [
                'submission_id' => $submission->id,
            ]);

            return;
        }

        try {
            Mail::to($recipient)->send(new JoinFormSubmissionReceived($submission));
        } catch (Throwable $exception) {
            Log::error('Join form submission notification failed to send.', [
                'submission_id' => $submission->id,
                'recipient' => $recipient,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
