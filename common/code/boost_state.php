<?php

# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt || https://www.boost.org/LICENSE_1_0.txt)

class BoostState_ParseError extends BoostException {}

class BoostState {
    static function load($file_path) {
        $state = Array();

        if ($file_path && is_file($file_path)) {
            $file = fopen($file_path, 'rb');
            while (true) {
                $c = fgetc($file);
                if ($c === false) { break; }
                if ($c == '#') {
                    fgets($file);
                } else if ($c == '(') {
                    $record_key = rtrim(fgets($file));
                    if (!$record_key) {
                        fclose($file);
                        throw new BoostState_ParseError();
                    }
                    $state[$record_key] = self::read_record($file);
                } else {
                    fclose($file);
                    throw new BoostState_ParseError();
                }
            }
            fclose($file);
        }

        return $state;
    }

    static function read_record($file) {
        $record = Array();

        # This function sometimes needs to lookahead at the first character in a
        # line, so always read it in advance.
        $c = fgetc($file);

        while (true) {
            if (!$c) { throw new BoostState_ParseError(); }

            if ($c == ')') {
                if (fgets($file) != "\n") { throw new BoostState_ParseError(); }
                return $record;
            }

            if ($c != '-') { throw new BoostState_ParseError(); }

            $key = rtrim(fgets($file));
            $c = fgetc($file);

            if ($c == ')' || $c == '-') {
                # The key has no value, so don't read anything. This '$c' will
                # be dealt with in the next loop.
                $record["$key"] = null;
            } else if ($c == '.') {
                $record["$key"] = floatval(trim(fgets($file)));
                $c = fgetc($file);
            } else if ($c == '!') {
                $record["$key"] = trim(fgets($file)) ? true : false;
                $c = fgetc($file);
            } else if ($c == '=') {
                $record["$key"] = intval(trim(fgets($file)));
                $c = fgetc($file);
            } else if ($c == '"') {
                $values = Array();
                while ($c == '"') {
                    $values[] = fgets($file);
                    $c = fgetc($file);
                }

                $record["$key"] = substr(implode('', $values), 0, -1);
            } else if ($c == '@') {
                $record["$key"] = new DateTime(fgets($file));
                $c = fgetc($file);
            } else {
                throw new BoostState_ParseError();
            }
        }
    }

    static function save($state, $file_path) {
        $file = fopen($file_path, "wb");
        ksort($state);
        foreach ($state as $record_key => $record) {
            fputs($file, "(");
            fputs($file, $record_key);
            fputs($file, "\n");

            ksort($record);
            foreach ($record as $key => $value) {
                fputs($file, "-");
                fputs($file, $key);
                fputs($file, "\n");

                if ($value !== null) {
                    if (is_string($value)) {
                        fputs($file, '"');
                        fputs($file, str_replace("\n", "\n\"", $value));
                        fputs($file, "\n");
                    } else if (is_bool($value)) {
                        fputs($file, '!');
                        fputs($file, $value ? 1 : 0);
                        fputs($file, "\n");
                    } else if (is_int($value)) {
                        fputs($file, '=');
                        fputs($file, $value);
                        fputs($file, "\n");
                    } else if (is_float($value)) {
                        fputs($file, '.');
                        fputs($file, $value);
                        fputs($file, "\n");
                    } else if ($value instanceof \DateTime || $value instanceof \DateTimeInterface) {
                        fputs($file, '@');
                        fputs($file, $value->format(DATE_RSS));
                        fputs($file, "\n");
                    } else {
                        print_r($value);
                        assert(false);
                    }
                }
            }
            fputs($file, ")\n");
        }

        fclose($file);
    }

    static function load_json($file_path) {
        if ($file_path && is_file($file_path)) {
            $v = json_decode(file_get_contents($file_path), true);
            if (is_null($v)) { throw new BoostState_ParseError(); }
            return $v;
        } else {
            return array();
        }
    }

    static function save_json($state, $file_path) {
        $file = fopen($file_path, "wb");
        ksort($state);
        fputs($file, "{\n");
        $first_record = true;
        foreach ($state as $record_key => $record) {
            if (!$first_record) { fputs($file, ",\n"); }
            $first_record = false;

            fputs($file, "    ");
            fputs($file, json_encode($record_key));
            fputs($file, ": {\n");

            ksort($record);
            $first = true;
            foreach ($record as $key => $value) {
                if (!$first) { fputs($file, ",\n"); }
                $first = false;

                fputs($file, "        ");
                fputs($file, json_encode($key));
                fputs($file, ":\n");
                fputs($file, "            ");

                if (is_float($value)) {
                    $v = json_encode($value);
                    if (ctype_digit($v)) { $v .= '.0'; }
                    fputs($file, $v);
                } else if ($value instanceof \DateTime || $value instanceof \DateTimeInterface) {
                    // Will load as a string, but can be decoded.
                    fputs($file, json_encode($value->format(DATE_RSS)));
                } else {
                    // Should possibly check that this is an atom.
                    // Maybe write a recursive thing?
                    fputs($file, json_encode($value));
                }
            }
            fputs($file, "\n");
            fputs($file, "    }");
        }
        fputs($file, "\n");
        fputs($file, "}\n");
        fclose($file);
    }
}
