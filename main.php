
<?php
function ExecuteHTTPGetRequest($url, $deleteAllTags)
{
    $url = parse_url($url);
    $host = $url["host"];
    if(isset($url["path"]))
    $path = $url["path"];
    else $path = '/';
    $req = "GET $path HTTP/1.1\r\nUser-Agent: Bulbulator\r\n"
        . "Host: $host\r\nConnection: Close\r\n\r\n";

    $fp = fsockopen($host, 80, $errno, $errstr); // открываем сокет
    error_reporting(E_ERROR);
    if (!$fp)
    {
        return"Error.";
    }
    else
    {
        // отправляем запрос
        fputs($fp, $req);
        // считывает полученные данные в переменную по 256 байт
        $data = "";
        while (!feof($fp))
        {
            $data .= @fgetss($fp, 256, $deleteAllTags === true ? "<a>" : NULL);
        }
        fclose($fp);
        return $data; // возвращает полученные данные
    }
}
$taskUrls = array(); // очередь адресов

$analysUrls = array(); // адреса для анализ
function replaceEregWithPregMatch($path) {
    $content = file_get_contents($path);
    $content = preg_replace('/ereg\(("|\')(.+)(\"|\'),/',
        "preg_match('/$2/',",
        $content);
    file_put_contents($path, $content);
}
function SearchUrls($host, $source)
{
    global $taskUrls, $analysUrls;
    // шаблон регулярного выражения для поиска URL
    $pattern = "#href\s*=\s*(\"|\\')?(.*?)(\"|\\'|\s|\>)#si";
    preg_match_all($pattern, $source, $mtchs);
    if (count($mtchs) < 3) return; //
    foreach ($mtchs[2] as $k => $v) {
        if (strpos(strtolower($v), "mailto:") === false
            && strpos(strtolower($v), "javascript:") === false) {
            $newUrl = $v;
            $isNew = true;

            if (preg_match("/[a-zA-Z]+:\/\/(.*)/", $newUrl) === false) { // если адрес начинается не с http://, анализируем и добавляем http://
                $newUrl = strtr(trim($newUrl), "\\", "/");
                $arrPath = explode("/", $newUrl); // разбивается путь на части для анализа
                $newUrl = "";
                foreach ($arrPath as $kk => $vv) {
                    if ($vv != ".") {
                        if (!$kk && (strlen($vv) > 1 && $vv[1] === ":" || $vv === "")) {
                            $newUrl = $vv;
                        } elseif ($vv === "..") {
                            if (strlen($newUrl) > 1 && $newUrl[1] === ":") {
                                continue;
                            }
                            $p = dirname($newUrl);
                            if ($p === "/" || $p === "\\" || $p === ".") {
                                $newUrl = "";
                            } else {
                                $newUrl = $p;
                            }
                        } elseif ($vv !== "") {
                            $newUrl .= "/$vv";
                        }
                    }
                }
                $newUrl = $newUrl !== "" ? $newUrl : "/";
                $newUrl = "http://" . $host . $newUrl;
            }
            $nu = parse_url($newUrl);
            if(isset($nu["host"]))
            $isNew = ($nu["host"] === $host);
            if ($isNew && $taskUrls != NULL) $isNew =
                !in_array($newUrl, $taskUrls);
            // добавление в список заданий
            if ($isNew) $taskUrls[] = $newUrl;
            // если адрес имеет параметры,
            // добавляем в список для поиска SQL Injection
            if (strpos($newUrl, "?") && !in_array($newUrl, $analysUrls)) {
                $isNew = true;
                foreach ($analysUrls as $k => $v) {
                    if (strtolower(strrev(stristr(strrev($v), "?")))
                        === strtolower(strrev(stristr(strrev($newUrl), "?")))) {
                        $isNew = false;
                        break;
                    }
                    }
                    if ($isNew) $analysUrls[0] = $newUrl;
                }
            }
        }
}
function Process($taskUrls)
{
    $masForJs = [];
    global  $analysUrls;
  if (count($taskUrls) <= 0)
  {
      $masForJs['error'] = "Адреса для сканирования не найдены.";
      return $masForJs;
  }
   for ($i = 0; $i < count($taskUrls); $i++)
  {
      $nu = parse_url($taskUrls[$i]);
      if(isset($nu["host"]))
      SearchUrls($nu["host"], ExecuteHTTPGetRequest($taskUrls[$i], true));
      if ($i > 49) break; // сканирует 50 страниц
  }

  // поиск уязвимости на страницах
  $masForJs['scan'] = count($analysUrls);
  // слова в сообщениях об ошибках при работе с БД
  $errorMessages = array(
      "sql syntax", "sql error",
      "ole db error", "incorrect syntax", "unclosed quotation mark",
      "sql server error", "microsoft jet database engine error",
      "'microsoft.jet.oledb.4.0' reported an error", "reported an error",
      "provider error", "oracle database error", "database error",
      "db error", "syntax error in string in query expression",
      "ошибка синтаксиса", "синтаксическая ошибка",
      "ошибка бд", "ошибочный запрос", "ошибка доступа к бд"
  );
  foreach ($analysUrls as $k => $v)
  {
      $masForJs['ref'] = "$v";
      $data = ExecuteHTTPGetRequest(str_replace("=", "='", $v), false);
      foreach ($errorMessages as $ek => $ev)
      {
          if (stripos($data, $ev) !== false)
          {
              $masForJs['sql'] = "SQL Injection!";
              break;
          }
      }
  }

  return $masForJs;
}
set_time_limit(0); // выставил максимальное время выполнения скрипта// добавляю url в список заданий
