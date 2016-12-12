<?php

namespace HackathonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('HackathonBundle:Default:index.html.twig');
    }

    /**
     * @Route("/get", name="getAnswer")
     */

    public function answer(Request $request)
    {
        $s = $request->get('question');

        if(preg_match('(^recherche)',$s)){
            $search = explode('recherche', $s);
            $url = 'http://api.redtube.com/?data=redtube.Videos.searchVideos&output=json&search='.urlencode($search[1]).'&thumbsize=all';
            $json = json_decode(file_get_contents($url));

            if($json->message == "No Videos found!"){
                $json = json_encode(array('url' => $url, 'rep' => 'Rien.. :('));
                return new Response($json);
            }

            $allVideos = array();
            foreach($json->videos as $video){
                array_push($allVideos, $video->video->url);
            }

            $s = array_rand($allVideos);
            $json = json_encode(array('url' => $url, 'rep' => '<a href="'.$allVideos[$s].'">Petit coquin</a>'));
            return new Response($json);
        }

        if(preg_match('(^gif)',$s)){
            $search = explode('gif', $s);
            $url = 'http://api.giphy.com/v1/gifs/search?q='.urlencode($search[1]).'&api_key=dc6zaTOxFJmzC';
            $json = json_decode(file_get_contents($url));

            if(empty($json->data)){
                $json = json_encode(array('url' => $url, 'rep' => 'Rien.. :('));
                return new Response($json);
            }

            $allGifs = array();
            foreach($json->data as $gifs){
                array_push($allGifs, $gifs->images->original->url);
            }

            $s = array_rand($allGifs);
            $json = json_encode(array('url' => $url, 'rep' => '<img src="'.$allGifs[$s].'">'));
            return new Response($json);
        }

        $factory = new ChatterBotFactory();

        $bot1 = $factory->create(ChatterBotType::CLEVERBOT);
        $bot1session = $bot1->createSession('fr');

        $s = $bot1session->think($s);
        $url = "https://api.naturalreaders.com/v2/tts/?t=" . urlencode($s) . "&r=21&s=1&requesttoken=9b15e67917d975b26e414926a1ec37d";
        $json = json_encode(array('url' => $url, 'rep' => $s));
        return new Response($json);
    }
}

class ChatterBotType
{
    const CLEVERBOT = 1;
    const JABBERWACKY = 2;
    const PANDORABOTS = 3;
}

class ChatterBotFactory
{
    public function create($type, $arg = null)
    {
        switch ($type)
        {
            case ChatterBotType::CLEVERBOT:
            {
                return new _Cleverbot('http://www.cleverbot.com', 'http://www.cleverbot.com/webservicemin?uc=321', 26);
            }
            case ChatterBotType::JABBERWACKY:
            {
                return new _Cleverbot('http://jabberwacky.com', 'http://jabberwacky.com/webservicemin', 20);
            }
            case ChatterBotType::PANDORABOTS:
            {
                if ($arg == null) {
                    throw new Exception('PANDORABOTS needs a botid arg');
                }
                return new _Pandorabots($arg);
            }
        }
    }
}

abstract class ChatterBot
{
    public function createSession($lang = 'en')
    {
        return null;
    }
}

abstract class ChatterBotSession
{
    public function thinkThought($thought)
    {
        return $thought;
    }

    public function think($text)
    {
        $thought = new ChatterBotThought();
        $thought->setText($text);
        return $this->thinkThought($thought)->getText();
    }
}

class ChatterBotThought
{
    private $text;

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }
}

#################################################
# Cleverbot impl
#################################################

class _Cleverbot extends ChatterBot
{
    private $baseUrl;
    private $serviceUrl;
    private $endIndex;

    public function __construct($baseUrl, $serviceUrl, $endIndex)
    {
        $this->baseUrl = $baseUrl;
        $this->serviceUrl = $serviceUrl;
        $this->endIndex = $endIndex;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }

    public function getEndIndex()
    {
        return $this->endIndex;
    }

    public function setEndIndex($endIndex)
    {
        $this->endIndex = $endIndex;
    }

    public function createSession($lang = 'en')
    {
        return new _CleverbotSession($this, $lang);
    }
}

class _CleverbotSession extends ChatterBotSession
{
    private $bot;
    private $headers;
    private $cookies;
    private $vars;

    public function __construct($bot, $lang)
    {
        $this->bot = $bot;
        $this->headers = array();
        $this->headers['Accept-Language'] = "$lang;q=1.0";
        $this->vars = array();
        //$this->vars['start'] = 'y';
        $this->vars['stimulus'] = '';
        $this->vars['islearning'] = '1';
        $this->vars['icognoid'] = 'wsf';
        //$this->vars['fno'] = '0';
        //$this->vars['sub'] = 'Say';
        //$this->vars['cleanslate'] = 'false';
        $this->cookies = array();
        _utils_request($this->bot->getBaseUrl(), $this->cookies, null, $this->headers);
    }

    public function thinkThought($thought)
    {
        $this->vars['stimulus'] = $thought->getText();
        $data = http_build_query($this->vars);
        $dataToDigest = substr($data, 9, $this->bot->getEndIndex());
        $dataDigest = md5($dataToDigest);
        $this->vars['icognocheck'] = $dataDigest;
        $response = _utils_request($this->bot->getServiceUrl(), $this->cookies, $this->vars, $this->headers);
        $responseValues = explode("\r", $response);
        //self.vars['??'] = _utils_string_at_index($responseValues, 0);
        $this->vars['sessionid'] = _utils_string_at_index($responseValues, 1);
        $this->vars['logurl'] = _utils_string_at_index($responseValues, 2);
        $this->vars['vText8'] = _utils_string_at_index($responseValues, 3);
        $this->vars['vText7'] = _utils_string_at_index($responseValues, 4);
        $this->vars['vText6'] = _utils_string_at_index($responseValues, 5);
        $this->vars['vText5'] = _utils_string_at_index($responseValues, 6);
        $this->vars['vText4'] = _utils_string_at_index($responseValues, 7);
        $this->vars['vText3'] = _utils_string_at_index($responseValues, 8);
        $this->vars['vText2'] = _utils_string_at_index($responseValues, 9);
        $this->vars['prevref'] = _utils_string_at_index($responseValues, 10);
        //$this->vars['??'] = _utils_string_at_index($responseValues, 11);
//            $this->vars['emotionalhistory'] = _utils_string_at_index($responseValues, 12);
//            $this->vars['ttsLocMP3'] = _utils_string_at_index($responseValues, 13);
//            $this->vars['ttsLocTXT'] = _utils_string_at_index($responseValues, 14);
//            $this->vars['ttsLocTXT3'] = _utils_string_at_index($responseValues, 15);
//            $this->vars['ttsText'] = _utils_string_at_index($responseValues, 16);
//            $this->vars['lineRef'] = _utils_string_at_index($responseValues, 17);
//            $this->vars['lineURL'] = _utils_string_at_index($responseValues, 18);
//            $this->vars['linePOST'] = _utils_string_at_index($responseValues, 19);
//            $this->vars['lineChoices'] = _utils_string_at_index($responseValues, 20);
//            $this->vars['lineChoicesAbbrev'] = _utils_string_at_index($responseValues, 21);
//            $this->vars['typingData'] = _utils_string_at_index($responseValues, 22);
//            $this->vars['divert'] = _utils_string_at_index($responseValues, 23);
        $responseThought = new ChatterBotThought();
        $text = _utils_string_at_index($responseValues, 0);
        if (!is_null($text))
        {
            $text = preg_replace_callback(
                '/\|([01234567890ABCDEF]{4})/',
                function ($matches) {
                    return iconv('UCS-4LE', 'UTF-8', pack('V', hexdec($matches[0])));
                },
                $text);
        }
        else
        {
            $text = '';
        }
        $responseThought->setText($text);
        return $responseThought;
    }
}

#################################################
# Pandorabots impl
#################################################

class _Pandorabots extends ChatterBot
{
    private $botid;

    public function __construct($botid)
    {
        $this->botid = $botid;
    }

    public function getBotid()
    {
        return $this->botid;
    }

    public function setBotid($botid)
    {
        $this->botid = $botid;
    }

    public function createSession($lang = 'en')
    {
        return new _PandorabotsSession($this);
    }
}

class _PandorabotsSession extends ChatterBotSession
{
    private $vars;

    public function __construct($bot)
    {
        $this->vars = array();
        $this->vars['botid'] = $bot->getBotid();
        $this->vars['custid'] = uniqid();
    }

    public function thinkThought($thought)
    {
        $this->vars['input'] = $thought->getText();
        $dummy = NULL;
        $response = _utils_request('http://www.pandorabots.com/pandora/talk-xml', $dummy, $this->vars);
        $element = new SimpleXMLElement($response);
        $result = $element->xpath('//result/that/text()');
        $responseThought = new ChatterBotThought();
        if (isset($result[0][0]))
        {
            $responseThought->setText(trim($result[0][0]));
        }
        else
        {
            $responseThought->setText("");
        }
        return $responseThought;
    }
}

#################################################
# Utils
#################################################

function _utils_request($url, &$cookies, $params, $headers = null)
{
    $contextParams = array();
    $contextParams['http'] = array();
    if ($params)
    {
        $contextParams['http']['method'] = 'POST';
        $contextParams['http']['content'] = http_build_query($params);
        $contextParams['http']['header'] = "Content-type: application/x-www-form-urlencoded\r\n";
    }
    else
    {
        $contextParams['http']['method'] = 'GET';
    }
    if (!is_null($cookies) && count($cookies) > 0)
    {
        $cookieHeader = "Cookie: ";
        foreach ($cookies as $cookieName => $cookie)
        {
            $cookieHeader .= $cookie . ";";
        }
        $cookieHeader .= "\r\n";
        if (isset($contextParams['http']['header']))
        {
            $contextParams['http']['header'] .= $cookieHeader;
        }
        else
        {
            $contextParams['http']['header'] = $cookieHeader;
        }
    }
    if (!is_null($headers))
    {
        foreach ($headers as $headerName => $headerValue)
        {
            if (isset($contextParams['http']['header']))
            {
                $contextParams['http']['header'] .= "$headerName: $headerValue\r\n";
            }
            else
            {
                $contextParams['http']['header'] = "$headerName: $headerValue\r\n";
            }
        }
    }
    $context = stream_context_create($contextParams);
    $fp = fopen($url, 'rb', false, $context);
    $response = stream_get_contents($fp);
    if (!is_null($cookies))
    {
        foreach ($http_response_header as $header)
        {
            if (preg_match('@Set-Cookie: (([^=]+)=[^;]+)@i', $header, $matches))
            {
                $cookies[$matches[2]] = $matches[1];
            }
        }
    }
    fclose($fp);
    return $response;
}

function _utils_string_at_index($strings, $index)
{
    if (count($strings) > $index)
    {
        return $strings[$index];
    }
    else
    {
        return '';
    }
}