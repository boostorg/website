<?php

# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt || http://www.boost.org/LICENSE_1_0.txt)

class BoostStateParseError extends RuntimeException {}

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
                        throw new BoostStateParseError();
                    }
                    $state[$record_key] = self::read_record($file);
                } else {
                    fclose($file);
                    throw new BoostStateParseError();
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
            if (!$c) { throw new BoostStateParseError(); }

            if ($c == ')') {
                if (fgets($file) != "\n") { throw new BoostStateParseError(); }
                return $record;
            }

            if ($c != '-') { throw new BoostStateParseError(); }

            $key = rtrim(fgets($file));
            $c = fgetc($file);

            if ($c == ')' || $c == '-') {
                # The key has no value, so don't read anything. This '$c' will
                # be dealt with in the next loop.
                $record["$key"] = null;
            } else if ($c == '.') {
                $record["$key"] = floatval(fgets($file));
                $c = fgetc($file);
            } else if ($c == '!') {
                $record["$key"] = boolval(fgets($file));
                $c = fgetc($file);
            } else if ($c == '=') {
                $record["$key"] = intval(fgets($file));
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
                throw new BoostStateParseError();
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
}
