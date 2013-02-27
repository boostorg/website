#!/usr/bin/env python
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import os, sys

class StateParseError(BaseException):
    None

def load(file_path):
    state = {}

    if file_path and os.path.isfile(file_path):
        file = open(file_path)
        try:
            while (True):
                c = file.read(1)
                if not c:
                    break
                if c == '#':
                    file.readline()
                    continue
                elif c == '(':
                    record_key = file.readline().rstrip()
                    if not record_key: raise StateParseError()
                    state[record_key] = read_record(file)
                else:
                    raise StateParseError()
        finally:
            file.close()

    return state

def read_record(file):
    record = {}
    
    # This function sometimes needs to lookahead at the first character in a
    # line, so always read it in advance.
    c = file.read(1)

    while (True):
        if not c: raise StateParseError()

        if c == ')':
            if file.readline() != '\n': raise StateParseError()
            return record

        if c != '-': raise StateParseError()

        key = file.readline().rstrip()
        c = file.read(1)

        if c == ')' or c == '-':
            # The key has no value, so don't read anything. This 'c' will
            # be dealt with in the next loop.
            record[key] = None
        elif c == '.':
            record[key] = float(file.readline())
            c = file.read(1)
        elif c == '!':
            record[key] = bool(file.readline())
            c = file.read(1)
        elif c == '=':
            record[key] = int(file.readline())
            c = file.read(1)
        elif c == '"':
            value = []
            while c == '"':
                if sys.version_info < (3, 0):
                    value.append(file.readline().decode('utf-8'))
                else:
                    value.append(file.readline())

                c = file.read(1)

            record[key] = (''.join(value))[:-1]
        else:
            raise StateParseError()

def save(state, file_path):
    file = open(file_path, "wb")
    try:
        for record_key in sorted(state.keys()):
            record = state[record_key]

            write(file, "(")
            write(file, record_key)
            write(file, "\n")

            for key in sorted(record.keys()):            
                write(file, "-")
                write(file, key)
                write(file, "\n")

                if record[key] is not None:
                    if isinstance(record[key], str) or \
                            (sys.version_info < (3,0) and isinstance(record[key], unicode)):
                        write(file, '"')
                        write(file, record[key].replace("\n", "\n\""))
                        write(file, "\n")
                    elif isinstance(record[key], bool):
                        write(file, '!')
                        write(file, str(record[key]))
                        write(file, "\n")
                    elif isinstance(record[key], int):
                        write(file, '=')
                        write(file, str(record[key]))
                        write(file, "\n")
                    elif isinstance(record[key], float):
                        write(file, '.')
                        write(file, str(record[key]))
                        write(file, "\n")
                    else:
                        print(type(record[key]))
                        assert False

            write(file, ")\n")
    finally:
        file.close()

def write(file, str):
    file.write(str.encode('utf-8'))
