<?php
error_reporting(0);
ignore_user_abort(true);
set_time_limit(60000);
ini_set("max_execution_time", 60000);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: *");

final class MonarxSecuritySiteAnalyzer
{
    private $instructions;

    public function __construct()
    {
        $req_body = $this->getRequestBody();

        if (is_array($req_body)) {
            $req_body["file_hash"] = $this->getFileHash();
        }

        $endpoint = $this->getEndpoint($req_body);
        $this->instructions = $this->httpPost($endpoint, $req_body);
    }

    private function getEndpoint($req_body)
    {
        $subdomain = "";
        $subdomains = [
            "mx-prod" => "",
            "mx-stage" => "stage",
            "mx-dev" => "dev",
        ];

        if (
            isset($req_body["env"]) &&
            array_key_exists($req_body["env"], $subdomains)
        ) {
            $subdomain = $subdomains[$req_body["env"]];
        }

        if (strlen($subdomain) > 0) {
            $subdomain = ".$subdomain";
        }

        return "https://api$subdomain.monarx.com/v1/intelligence/site-analysis/register";
    }

    private function getRequestBody()
    {
        $input = file_get_contents("php://input");

        if ($input === false) {
            $this->handleError("Failed to read input");
        }

        $decoded = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->handleError("Logging off. Goodbye!", true);
        }

        return $decoded;
    }

    private function getFileHash()
    {
        $file_path = __FILE__;
        $file_contents = file_get_contents($file_path);

        if ($file_contents === false) {
            $this->handleError("Failed to load checksum");
        }

        return hash("sha256", $file_contents);
    }

    private function httpPost($url, $data)
    {
        $payload = json_encode($data);

        if ($payload === false) {
            $this->handleError("Failed to encode payload");
        }

        $ch = curl_init($url);

        if ($ch === false) {
            $this->handleError("Failed to initialize request");
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_USERAGENT, "Monarx Security");

        $result = curl_exec($ch);
        if ($result === false) {
            curl_close($ch);
            $this->handleError("Failed to connect", true);
        }

        curl_close($ch);
        return $result;
    }

    public function run()
    {
        if (!empty($this->instructions)) {
            if ($this->validateInstructions($this->instructions)) {
                eval($this->instructions);
            } else {
                $this->handleError("Invalid instructions received", true);
            }
        } else {
            $this->handleError("No instructions received", true);
        }
    }

    private function validateInstructions($instructions)
    {
        return is_string($instructions);
    }

    private function handleError($message, $deleteSelf = false)
    {
        echo json_encode(array("error" => $message, "success" => false));

        if ($deleteSelf) {
            @unlink(__FILE__);
        }

        exit();
    }
}

try {
    $mnx = new MonarxSecuritySiteAnalyzer();
    $mnx->run();
} catch (Exception $e) {
    $error_message = "Unknown error occurred";
    echo json_encode(array("error" => $error_message, "success" => false));
    @unlink(__FILE__);
}
?>
