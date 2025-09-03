<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;

/**
 * @OA\Info(
 *     title="QR Code Generation API",
 *     version="1.0.0",
 *     description="API for generating QR codes (authentication temporarily disabled)",
 *     @OA\Contact(
 *         email="api@example.com",
 *         name="API Support"
 *     )
 * )
 *
 * Security scheme commented out - authentication temporarily disabled
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Authentication is temporarily disabled"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 *
 * @OA\Server(
 *     url="https://qr.cma.gov.ae",
 *     description="Production server"
 * )
 */
class QrCodeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/generate-qr",
     *     operationId="generateQrGet",
     *     tags={"QR Code Generation"},
     *     summary="Generate QR code via GET request",
     *     description="Generates a QR code image for the provided URL with optional size customization.",
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         required=true,
     *         description="The URL to encode in the QR code",
     *         @OA\Schema(
     *             type="string",
     *             format="url",
     *             example="https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         required=false,
     *         description="Size of the QR code in pixels (default: 300)",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=50,
     *             maximum=1000,
     *             default=300,
     *             example=100
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code image generated successfully",
     *         @OA\MediaType(
     *             mediaType="image/png",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary",
     *                 description="PNG image data"
     *             )
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             @OA\Schema(type="string", example="image/png")
     *         ),
     *         @OA\Header(
     *             header="Cache-Control",
     *             @OA\Schema(type="string", example="public, max-age=3600")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized access")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error - Invalid URL",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid URL")
     *         )
     *     )
     * )
     */
    public function generateQr(Request $request)
    {
        // Debug: Log authentication status - COMMENTED OUT BUT KEPT FOR FUTURE USE
        // \Log::info('QR Generation Request', [
        //     'authenticated' => auth()->check(),
        //     'user_id' => auth()->id(),
        //     'has_bearer' => $request->bearerToken() ? 'yes' : 'no',
        //     'ip' => $request->ip(),
        //     'user_agent' => $request->userAgent()
        // ]);
        
        // Double-check authentication - COMMENTED OUT BUT KEPT FOR FUTURE USE
        // if (!auth()->check()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Unauthenticated. Please provide a valid bearer token.',
        //         'error' => 'Invalid or missing authentication token'
        //     ], 401)->header('Access-Control-Allow-Origin', '*')
        //        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        //        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        // }
        
        // Validate URL
        if (!isset($request->url) || !filter_var(filter_var($request->url, FILTER_SANITIZE_URL), FILTER_VALIDATE_URL)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid URL'
            ], 422)->header('Access-Control-Allow-Origin', '*')
                   ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                   ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        $file_extension = 'png';
        $image_size = 300;
        
        if (isset($request->size) && ((int) $request->size > 0)) {
            $image_size = $request->size;
        }

        try {
            $qrCodeImage = QrCode::size($image_size)
                                 ->color(56, 88, 112)
                                 ->format($file_extension)
                                 ->errorCorrection('H')
                                 ->generate($request->url);

            return response($qrCodeImage, 200)
                ->header('Content-Type', 'image/png')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422)->header('Access-Control-Allow-Origin', '*')
                   ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                   ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
    }

}