<?php

/*
  Copyright 2015 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

/* This class doesn't really support the maintainers file in Boost 1.35.0.
 * The format has been consistent since Boost 1.36.0 so I'm not too worried. */
class BoostMaintainers
{
    var $maintainers;

    /** Read maintainers from the text contents of the file, or an iterable
     *  over the lines.
     */
    static function read_from_text($lines) {
        $boost_maintainers = new BoostMaintainers();

        if (!$lines) {
            $boost_maintainers->maintainers = array();
            return $boost_maintainers;
        }

        if (is_string($lines)) {
            $lines = explode("\n", $lines);
        }

        $maintainers = array();

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line || $line[0] == '#') {
                continue;
            }

            $matches = null;
            if (!preg_match('@^([^\s]+)\s*(.*)$@', $line, $matches)) {
                throw new BoostException("Unable to parse line: {$line}");
            }

            $key = trim($matches[1]);
            $values = trim($matches[2]);

            if ($key === 'logic') { $key = 'logic/tribool'; }
            if ($key === 'operators') { $key = 'utility/operators'; }
            if ($key === 'functional/foward') { $key = 'functional/forward'; }

            $maintainers[$key] = $values
                ? array_map('trim', explode(',', $values))
                : array();
        }

        $boost_maintainers->maintainers = $maintainers;
        return $boost_maintainers;
    }

    function write_to_text() {
        $output = <<<EOL
# Copyright (C) 2005, 2007  Douglas Gregor <doug.gregor -at- gmail.com>
# Distributed under the Boost Software License, Version 1.0. 
# See www.boost.org/LICENSE_1_0.txt
#
# This file lists the names and e-mail addresses of the maintainers
# of each Boost library, and is used by the regression-reporting 
# scripts to direct e-mail related to those libraries to the 
# maintainers.
#
# This file is automatically updated from library metadata.


EOL;
        $maintainers = array();
        foreach ($this->maintainers as $key => $people) {
            if ($key === 'logic/tribool') { $key = 'logic'; }
            if ($key === 'utility/operators') { $key = 'operators'; }
            if ($key === 'functional/foward') { $key = 'functional/forward'; }
            $maintainers[$key] = $people;
        }

        ksort($maintainers);
        foreach ($maintainers as $key => $people) {
            if ($people) {
                if (strlen($key) < 22) {
                    $output .= str_pad($key, 22);
                } else {
                    $output .= $key."  ";
                }
                $output .= implode(', ', $people);
            } else {
                $output .= $key;
            }
            $output .= "\n";
        }

        $output .= "\n\n\n";

        return $output;
    }

    function update_maintainer($key, $maintainers) {
        $this->maintainers[$key] = $maintainers;
    }
}
