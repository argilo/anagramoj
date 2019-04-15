<?php
  /*
    Copyright 2019 Clayton Smith (argilo@gmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
  */

  header("Access-Control-Allow-Origin: *");

  const X_SISTEMO = array("cx", "gx", "hx", "jx", "sx", "ux");
  const UNIKODO = array("ĉ", "ĝ", "ĥ", "ĵ", "ŝ", "ŭ");
  const KODO = array("q", "w", "[", "\\", "x", "y");

  function kodigu($literoj) {
    $literoj = mb_strtolower($literoj);
    $literoj = str_replace(X_SISTEMO, UNIKODO, $literoj);
    $literoj = str_replace(UNIKODO, KODO, $literoj);

    $novaj_literoj = array();
    foreach (str_split($literoj) as $litero) {
      $ord = ord($litero);
      if ($ord == 91 || $ord == 92 || ($ord >= 97 && $ord <= 122)) {
        array_push($novaj_literoj, $litero);
      }
    }
    return implode($novaj_literoj);
  }

  function faru_regex($literoj) {
    $vortoj = explode(" ", $literoj);

    $novaj_vortoj = array();
    foreach ($vortoj as $vorto) {
      array_push($novaj_vortoj, preg_quote(kodigu($vorto)));
    }

    return "/\\b(" . implode("|", $novaj_vortoj) . ")\\b/";
  }

  if (!empty($_POST["vorto"])) {
    $vorto = kodigu($_POST["vorto"]);
    $inkluzivu = "";

    if (!empty($_POST["inkluzivu"])) {
      $inkluzivu = kodigu($_POST["inkluzivu"]);
    }

    if (strlen($vorto) - strlen($inkluzivu) <= 25) {
      $parametroj = "-s -f vortoj.txt";
      if (!empty($_POST["maksvortoj"])) {
        $parametroj .= " -d" . intval($_POST["maksvortoj"]);
      }
      if (!empty($_POST["maksliteroj"])) {
        $parametroj .= " -m" . intval($_POST["maksliteroj"]);
      }
      if (!empty($_POST["minliteroj"])) {
        $parametroj .= " -n" . intval($_POST["minliteroj"]);
      }
      if (!empty($_POST["kandidatoj"]) && $_POST["kandidatoj"] == "jes") {
        $parametroj .= " -l -x";
      }
      if (!empty($_POST["inkluzivu"])) {
        $parametroj .= " -w " . escapeshellarg($inkluzivu);
      }

      $rezulto = shell_exec("./wordplay $parametroj " . escapeshellarg($vorto));
      $rezulto = strtolower($rezulto);

      if (!empty($_POST["ekskluzivu"])) {
        $regex = faru_regex($_POST["ekskluzivu"]);

        $nova_rezulto = array();
        foreach (explode("\n", $rezulto) as $anagramo) {
          if (preg_match($regex, $anagramo) === 0) {
            array_push($nova_rezulto, $anagramo);
          }
        }
        $rezulto = implode("\n", $nova_rezulto);
      }

      $rezulto = str_replace(KODO, UNIKODO, $rezulto);
      $rezulto = str_replace("\n", "<br>\n", rtrim($rezulto));
      echo $rezulto . "\n";
    } else {
      http_response_code(400);
    }
  } else {
    http_response_code(400);
  }
?>
