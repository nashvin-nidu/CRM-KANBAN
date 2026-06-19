<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Twilio WhatsApp webhook.
     */
    public function twilio(Request $request)
    {
        Log::info('Twilio webhook received', $request->all());
        return $this->processNormalizedRequest($request, 'WhatsApp', 'WhatsApp/Twilio');
    }

    /**
     * Handle Google Forms webhook.
     */
    public function googleForm(Request $request)
    {
        Log::info('Google Form webhook received', $request->all());
        return $this->processNormalizedRequest($request, 'Google Form', 'Google Forms');
    }

    /**
     * Handle Facebook verification (GET) and payload (POST).
     */
    public function facebook(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->verifyMetaWebhook($request, 'fb_crm_verify_token_2026');
        }

        Log::info('Facebook webhook received', $request->all());
        return $this->processNormalizedRequest($request, 'Facebook', 'Facebook Ads');
    }

    /**
     * Handle Instagram verification (GET) and payload (POST).
     */
    public function instagram(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->verifyMetaWebhook($request, 'ig_crm_verify_token_2026');
        }

        Log::info('Instagram webhook received', $request->all());
        return $this->processNormalizedRequest($request, 'Instagram', 'Instagram Ads');
    }

    /**
     * Dynamically process and normalize requests from any format.
     */
    private function processNormalizedRequest(Request $request, string $source, string $defaultCompany)
    {
        $allData = $request->all();

        // Normalize keys (case-insensitive, strips spaces/dashes/underscores)
        $name = $this->findValue($allData, ['name', 'fullname', 'profilename', 'username', 'contactname']);
        if (!$name) {
            $firstName = $this->findValue($allData, ['firstname', 'first']);
            $lastName = $this->findValue($allData, ['lastname', 'last']);
            if ($firstName) {
                $name = trim($firstName . ' ' . ($lastName ?? ''));
            }
        }

        // Normalize phone number
        $phone = $this->findValue($allData, ['phone', 'phonenumber', 'mobile', 'telephone', 'from', 'contactphone']);
        if ($phone) {
            $phone = str_replace('whatsapp:', '', $phone);
            $phone = trim($phone);
        }

        // Normalize email, company, value
        $email = $this->findValue($allData, ['email', 'emailaddress', 'contactemail']);
        $company = $this->findValue($allData, ['company', 'companyname', 'organization', 'org']) ?? $defaultCompany;
        $value = $this->findValue($allData, ['value', 'amount', 'dealvalue', 'price']) ?? 0;

        return $this->processLead([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'company' => $company,
            'source' => $source,
            'value' => (int) $value,
            'status' => 'New',
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Find key in nested array case-insensitively, supporting strip of underscores/dashes/spaces.
     */
    private function findValue(array $array, array $keys)
    {
        foreach ($keys as $key) {
            $lowerKey = strtolower($key);

            // First search at current level
            foreach ($array as $k => $v) {
                $normalizedK = strtolower(str_replace(['_', '-', ' '], '', $k));
                if ($normalizedK === $lowerKey && $v !== null && $v !== '') {
                    return $v;
                }
            }
        }

        // Recursive search for nested structures (e.g. Meta webhooks entries)
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $found = $this->findValue($value, $keys);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Process extracted lead details.
     */
    private function processLead(array $data)
    {
        $name = isset($data['name']) ? trim($data['name']) : '';
        $phone = isset($data['phone']) ? trim($data['phone']) : '';

        // 1. Condition: both name and phone number is a must
        if (empty($name) || empty($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: Name and Phone number are required.',
            ], 422);
        }

        // 2. Condition: no duplicate data based on phone number
        $existsByPhone = Lead::where('phone', $phone)->exists();
        if ($existsByPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate lead: Phone number already exists.',
            ], 200); // 200 OK prevents webhook retries on duplicate
        }

        // 3. Condition: if name exists and any of number or email doesn't exist, update those fields
        $existingByName = Lead::where('name', $name)->first();
        if ($existingByName) {
            $updated = false;
            if (empty($existingByName->phone) && !empty($phone)) {
                $existingByName->phone = $phone;
                $updated = true;
            }
            if (empty($existingByName->email) && !empty($data['email'])) {
                $existingByName->email = $data['email'];
                $updated = true;
            }

            if ($updated) {
                $existingByName->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Lead updated successfully based on name match.',
                    'lead' => $existingByName,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Lead with this name already exists and has complete details.',
            ], 200);
        }

        // 4. Create new lead if it's a completely new name and phone number
        $lead = Lead::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully.',
            'lead' => $lead,
        ], 201);
    }

    /**
     * Verify Meta Webhook.
     */
    private function verifyMetaWebhook(Request $request, string $verifyToken)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Unauthorized', 403);
    }
}
