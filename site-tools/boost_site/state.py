#!/usr/bin/env python
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import os, sys

class StateParseError(BaseException):
    None

def load(file_path):
    state = {}

    if(file_path and os.path.isfile(file_path)):
        file = open(file_path)
        try:
            while (True):
                c = file.read(1)
                if(not c):
                    break
                if(c == '#'):
                    file.readline()
                    continue
                if(c != '('):
                    raise StateParseError()
                record_key = file.readline().rstrip()
                if(not record_key): raise StateParseError()
                record = {}
                key = None
                value = None
                type = None
                while (True):
                    c = file.read(1)
                    if((c == ')' or c == '-') and key):
                        if(not key):
                            raise StateParseError()

                        if(type == 'String'):
                            value = value[:-1]

                        record[key] = value

                    if(c == ')'):
                        if(file.readline() != '\n'): raise StateParseError()
                        break
                    elif(c == '-'):
                        key = file.readline().rstrip()
                        if(not key): raise StateParseError()
                        type = 'None'
                        value = None
                    elif(c == '.'):
                        if(not key or type != 'None'): raise StateParseError()
                        type = 'Float'
                        value = float(file.readline())
                    elif(c == '!'):
                        if(not key or type != 'None'): raise StateParseError()
                        type = 'Bool'
                        value = bool(file.readline())
                    elif(c == '='):
                        if(not key or type != 'None'): raise StateParseError()
                        type = 'Int'
                        value = int(file.readline())
                    elif(c == '"'):
                        if(not key): raise StateParseError()
                        if(type == 'None'):
                            type = 'String'
                            if sys.version_info < (3, 0):
                                value = file.readline().decode('utf-8')
                            else:
                                value = file.readline()
                        elif(type == 'String'):
                            if sys.version_info < (3, 0):
                                value = value + file.readline().decode('utf-8')
                            else:
                                value = value + file.readline()
                        else:
                            raise StateParseError()
                    else:
                        raise StateParseError()
                state[record_key] = record
        finally:
            file.close()

    return state

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
                    elif isinstance(record[key], (int, float)):
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