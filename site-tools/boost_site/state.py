#!/usr/bin/env python
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import os

class StateParseError:
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
                            value = file.readline().decode('utf-8')
                        elif(type == 'String'):
                            value = value + file.readline().decode('utf-8')
                        else:
                            raise StateParseError()
                    else:
                        raise StateParseError()
                state[record_key] = record
        finally:
            file.close()

    return state

def save(state, file_path):
    file = open(file_path, "w")
    try:
        for record_key in sorted(state.keys()):
            record = state[record_key]

            file.write("(")
            file.write(record_key)
            file.write("\n")

            for key in sorted(record.keys()):            
                file.write("-")
                file.write(key)
                file.write("\n")

                if record[key] is not None:
                    if isinstance(record[key], basestring):
                        file.write('"')
                        file.write(record[key].replace("\n", "\n\"").encode('utf-8'))
                        file.write("\n")
                    elif isinstance(record[key], bool):
                        file.write('!')
                        file.write(str(record[key]))
                        file.write("\n")
                    elif isinstance(record[key], (int, float)):
                        file.write('.')
                        file.write(str(record[key]))
                        file.write("\n")
                    else:
                        assert False

            file.write(")\n")
    finally:
        file.close()
