<?php
class MoodleAPI
{
    private $api_url;
    private $token;
    private $format;

    public function __construct()
    {
        $this->api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
        $this->token = "3f158134506350615397c83d861c2104";
        $this->format = "json";
    }

    private function callAPI($function, $params = [])
    {
        $params['wstoken'] = $this->token;
        $params['wsfunction'] = $function;
        $params['moodlewsrestformat'] = $this->format;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error en la llamada API: " . $error);
        }

        return json_decode($response, true);
    }

    public function createUser($userData)
    {
        $params = [
            'users[0][username]' => $userData['username'],
            'users[0][password]' => $userData['password'],
            'users[0][firstname]' => $userData['firstname'],
            'users[0][lastname]' => $userData['lastname'],
            'users[0][email]' => $userData['email'],
            'users[0][auth]' => 'manual',
            // Forzar cambio de contraseña en el primer login
            'users[0][preferences][0][type]' => 'auth_forcepasswordchange',
            'users[0][preferences][0][value]' => 1

        ];

        return $this->callAPI('core_user_create_users', $params);
    }

    public function enrollUserInCourses($userId, $courses)
    {
        $results = [];
        foreach ($courses as $courseId) {
            $params = [
                'enrolments[0][roleid]' => 5, // 5 = estudiante
                'enrolments[0][userid]' => $userId,
                'enrolments[0][courseid]' => $courseId
            ];

            $results[] = $this->callAPI('enrol_manual_enrol_users', $params);
        }
        return $results;
    }
}
