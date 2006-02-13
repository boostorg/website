<?php
require dirname(__FILE__) . '/../common/code/boost_version.php';

function libref($name,$l = NULL) {
  print '<a href="/doc/libs'.$_SERVER["PATH_INFO"].'/'.$l.'">'.$name.'</a>'; }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Boost C++ Libraries</title>
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-doc.css" />
  <!--[if IE]> <style type="text/css"> body { behavior: url(/style/csshover.htc); } </style> <![endif]-->
</head>

<body>
  <div id="heading">
    <?php virtual("/common/heading.html"); ?>
  </div>

  <div id="body">
    <div id="body-inner">
      <div id="content">
        <div class="section" id="intro">
          <div class="section-0">
            <div class="section-title">
              <h1>Boost C++ Libraries</h1>
            </div>

            <div class="section-body">
              <dl class="catalog">
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Any","doc/html/any.html"); ?></dt>

                <dd>Safe, generic container for single values of different
                value types, from Kevlin Henney.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Array","doc/html/array.html"); ?></dt>

                <dd>STL compliant container wrapper for arrays of constant
                size, from Nicolai Josuttis.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt>
                <?php libref("Assign","libs/assign/doc/index.html"); ?></dt>

                <dd>Filling containers with constant or generated data has
                never been easier, from Thorsten Ottosen.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Bind","libs/bind/bind.html"); ?> and
                <?php libref("Member Function","libs/bind/mem_fn.html"); ?></dt>

                <dd>Generalized binders for function/object/pointers and
                member functions, from Peter Dimov.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Call Traits","libs/utility/call_traits.htm"); ?></dt>

                <dd>Defines types for passing parameters, from John Maddock,
                Howard Hinnant, et al.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Compatibility","libs/compatibility/index.html"); ?></dt>

                <dd>Help for non-conforming standard libraries, from Ralf
                Grosse-Kunstleve and Jens Maurer.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Compressed Pair","libs/utility/compressed_pair.htm"); ?></dt>

                <dd>Empty member optimization, from John Maddock, Howard
                Hinnant, et al.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Concept Check","libs/concept_check/concept_check.htm"); ?></dt>

                <dd>Tools for generic programming, from Jeremy
                Siek.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Config","libs/config/config.htm"); ?></dt>

                <dd>Helps boost library developers adapt to compiler
                idiosyncrasies; not intended for library
                users.</dd><?php } ?><!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Conversion","libs/conversion/index.html"); ?></dt>

                <dd>Polymorphic and lexical casts, from Dave Abrahams and
                Kevlin Henney.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("CRC","libs/crc/index.html"); ?></dt>

                <dd>Cyclic Redundancy Code, from Daryle
                Walker.</dd><?php } ?><!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Date Time","doc/html/date_time.html"); ?></dt>

                <dd>Date-Time library from Jeff Garland.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Dynamic Bitset","libs/dynamic_bitset/dynamic_bitset.html"); ?></dt>

                <dd>A runtime sized version of <tt>std::bitset</tt> from
                Jeremy Siek and Chuck Allison.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Enable If","libs/utility/enable_if.html"); ?></dt>

                <dd>Selective inclusion of function template overloads, from
                Jaakko J&auml;rvi, Jeremiah Willcock, and Andrew
                Lumsdaine.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Filesystem","libs/filesystem/doc/index.htm"); ?></dt>

                <dd>Portable paths, iteration over directories, and other
                useful filesystem operations, from Beman
                Dawes.</dd><?php } ?><!-- --><?php if (boost_version(1,34,0)) { ?>

                <dt><?php libref("Foreach","doc/html/foreach.html"); ?></dt>

                <dd>BOOST_FOREACH macro for easily iterating over the
                elements of a sequence, from Eric Niebler.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Format","libs/format/index.html"); ?></dt>

                <dd>Type-safe 'printf-like' format operations, from Samuel
                Krempp.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Function","doc/html/function.html"); ?></dt>

                <dd>Function object wrappers for deferred calls or callbacks,
                from Doug Gregor.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Functional","libs/functional/index.html"); ?></dt>

                <dd>Enhanced function object adaptors, from Mark
                Rodgers.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,33,0)) { ?>

                <dt>
                <?php libref("Functional Hash","doc/html/hash.html"); ?></dt>

                <dd>A TR1 hash function object that can be extended to hash
                user defined types, from Daniel James.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Graph","libs/graph/doc/table_of_contents.html"); ?></dt>

                <dd>Generic graph components and algorithms, from Jeremy Siek
                and a University of Notre Dame team.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Integer","libs/integer/index.html"); ?></dt>

                <dd>Headers to ease dealing with integral
                types.</dd><?php } ?><!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Interval","libs/numeric/interval/doc/interval.htm"); ?></dt>

                <dd>Extends the usual arithmetic functions to mathematical
                intervals, from Guillaume Melquiond, Herv&acute;
                Br&ouml;nnimann and Sylvain Pion.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("In Place Factory, Typed In Place Factory","libs/utility/in_place_factories.html"); ?></dt>

                <dd>Generic in-place construction of contained objects with a
                variadic argument-list, from Fernando
                Cacciola.</dd><?php } ?><!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("IO State Savers","libs/io/doc/ios_state.html"); ?></dt>

                <dd>Save I/O state to prevent jumbled data, from Daryle
                Walker.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,33,0)) { ?>

                <dt>
                <?php libref("Iostreams","libs/iostreams/doc/index.html"); ?></dt>

                <dd>Framework for defining streams, stream buffers and i/o
                filters, from Jonathan Turkanis.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Iterators","libs/iterator/doc/index.html"); ?></dt>

                <dd>Iterator construction framework, adaptors, concepts, and
                more, from Dave Abrahams, Jeremy Siek, and Thomas
                Witt.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Lambda","doc/html/lambda.html"); ?></dt>

                <dd>Define small unnamed function objects at the actual call
                site, and more, from Jaakko J&auml;rvi and Gary
                Powell.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Math","libs/math/doc/index.html"); ?></dt>

                <dd>Several contributions in the domain of mathematics, from
                various authors.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Math Common Factor","libs/math/doc/common_factor.html"); ?></dt>

                <dd>Greatest common divisor and least common multiple, from
                Daryle Walker.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Math Octonion","libs/math/octonion/index.html"); ?></dt>

                <dd>Octonions, from Hubert Holin.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Math Quaternion","libs/math/quaternion/index.html"); ?></dt>

                <dd>Quaternions, from Hubert Holin.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Math Special Functions","libs/math/special_functions/index.html"); ?></dt>

                <dd>Mathematical special functions such as atanh, sinc, and
                sinhc, from Hubert Holin.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt>
                <?php libref("Min-Max","libs/algorithm/minmax/index.html"); ?></dt>

                <dd>standard library extensions for simultaneous min/max and
                min/max element computations, from Herv&eacute;
                Br&ouml;nnimann.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("MPL","libs/mpl/doc/index.html"); ?></dt>

                <dd>Template metaprogramming framework of compile-time
                algorithms, sequences and metafunction classes, from Aleksey
                Gurtovoy.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Multi-Array","libs/multi_array/doc/index.html"); ?></dt>

                <dd>Multidimensional containers and adaptors for arrays of
                contiguous data, from Ron Garcia.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt>
                <?php libref("Multi-Index","libs/multi_index/doc/index.html"); ?></dt>

                <dd>Containers with multiple STL-compatible access
                interfaces, from Joaqu&iacute;n M L&oacute;pez
                Mu&ntilde;oz.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt>
                <?php libref("Numeric Conversion","libs/numeric/conversion/doc/index.html"); ?></dt>

                <dd>Optimized Policy-based Numeric Conversions, from Fernando
                Cacciola.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Operators","libs/utility/operators.htm"); ?></dt>

                <dd>Templates ease arithmetic classes and iterators, from
                Dave Abrahams and Jeremy Siek.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Optional","libs/optional/doc/optional.html"); ?></dt>

                <dd>Discriminated-union wrapper for optional values, from
                Fernando Cacciola.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,33,0)) { ?>

                <dt>
                <?php libref("Parameter","libs/parameter/doc/html/index.html"); ?></dt>

                <dd>Write functions that accept arguments by name, by David
                Abrahams and Daniel Wallin.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,33,0)) { ?>

                <dt>
                <?php libref("Pointer Container","libs/ptr_container/doc/ptr_container.html"); ?></dt>

                <dd>Containers for storing heap-allocated polymorphic objects
                to ease OO-programming, from Thorsten Ottosen.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Pool","libs/pool/doc/index.html"); ?></dt>

                <dd>Memory pool management, from Steve Cleary.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Preprocessor","libs/preprocessor/doc/index.html"); ?></dt>

                <dd>Preprocessor metaprogramming tools including repetition
                and recursion, from Vesa Karvonen and Paul
                Mensonides.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt>
                <?php libref("Program Options","doc/html/program_options.html"); ?></dt>

                <dd>Access to configuration data given on command line, in
                config files and other sources, from Vladimir
                Prus.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Property Map","libs/property_map/property_map.html"); ?></dt>

                <dd>Concepts defining interfaces which map key objects to
                value objects, from Jeremy Siek.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Python","libs/python/doc/index.html"); ?></dt>

                <dd>Reflects C++ classes and functions into <a href=
                "http://www.python.org">Python</a>, from Dave
                Abrahams.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Random","libs/random/index.html"); ?></dt>

                <dd>A complete system for random number generation, from Jens
                Maurer.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt><?php libref("Range","libs/range/index.html"); ?></dt>

                <dd>A new infrastructure for generic algorithms that builds
                on top of the new iterator concepts, from Thorsten
                Ottosen.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Rational","libs/rational/index.html"); ?></dt>

                <dd>A rational number class, from Paul Moore.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Ref","doc/html/ref.html"); ?></dt>

                <dd>A utility library for passing references to generic
                functions, from Jaako J&auml;rvi, Peter Dimov, Doug Gregor,
                and Dave Abrahams.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Regex","libs/regex/doc/index.html"); ?></dt>

                <dd>Regular expression library, from John
                Maddock.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt>
                <?php libref("Serialization","libs/serialization/doc/index.html"); ?></dt>

                <dd>Serialization for persistence and marshalling, from
                Robert Ramey</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Signals","doc/html/signals.html"); ?></dt>

                <dd>managed signals &amp; slots callback implementation, from
                Doug Gregor.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Smart Ptr","libs/smart_ptr/smart_ptr.htm"); ?></dt>

                <dd>Five smart pointer class templates, from Greg Colvin,
                Beman Dawes, Peter Dimov, and Darin Adler.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Static Assert","doc/html/boost_staticassert.html"); ?></dt>

                <dd>Static assertions (compile time assertions), from John
                Maddock.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Spirit","libs/spirit/index.html"); ?></dt>

                <dd>LL parser framework&nbsp; represents parsers directly as
                EBNF grammars in inlined C++, from Joel de Guzman and
                team.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt>
                <?php libref("String Algo","doc/html/string_algo.html"); ?></dt>

                <dd>String algorithms library, from Pavol
                Droba</dd><?php } ?><!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Test","libs/test/doc/index.html"); ?></dt>

                <dd>Support for simple program testing, full unit testing,
                and for program execution monitoring, from Gennadiy
                Rozental.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Thread","doc/html/threads.html"); ?></dt>

                <dd>Portable C++ multi-threading, from William
                Kempf.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Timer","libs/timer/index.html"); ?></dt>

                <dd>Event timer, progress timer, and progress display
                classes, from Beman Dawes.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Tokenizer","libs/tokenizer/index.html"); ?></dt>

                <dd>Break of a string or other character sequence into a
                series of tokens, from John Bandela.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,32,0)) { ?>

                <dt><?php libref("Tribool","doc/html/tribool.html"); ?></dt>

                <dd>3-state boolean type library, from Doug
                Gregor.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Tuple","libs/tuple/doc/tuple_users_guide.html"); ?></dt>

                <dd>Ease definition of functions returning multiple values,
                and more, from Jaakko J&auml;rvi.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Type Traits","doc/html/boost_typetraits.html"); ?></dt>

                <dd>Templates for fundamental properties of types, from John
                Maddock, Steve Cleary, et al.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("uBLAS","libs/numeric/ublas/doc/index.htm"); ?></dt>

                <dd>Basic linear algebra for dense, packed and sparse
                matrices, from Joerg Walter and Mathias Koch.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Utility","libs/utility/utility.htm"); ?></dt>

                <dd>Class <b>noncopyable</b> plus <b>checked_delete()</b>,
                <b>checked_array_delete()</b>, <b>next(),</b>&nbsp;
                <b>prior()</b> function templates, plus <b>base-from-member
                idiom</b>, from Dave Abrahams and others.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt>
                <?php libref("Value Initialized","libs/utility/value_init.htm"); ?></dt>

                <dd>Wrapper for uniform-syntax value initialization, from
                Fernando Cacciola, based on the original idea of David
                Abrahams.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,31,0)) { ?>

                <dt><?php libref("Variant","doc/html/variant.html"); ?></dt>

                <dd>Safe, generic, stack-based discriminated union container,
                from Eric Friedman and Itay Maman.</dd><?php } ?>
                <!-- --><?php if (boost_version(1,33,0)) { ?>

                <dt><?php libref("Wave","libs/wave/index.html"); ?></dt>

                <dd>Standards conformant implementation of the mandated
                C99/C++ preprocessor functionality packed behind an easy to
                use iterator interface, from
                Hartmut&nbsp;Kaiser</dd><?php } ?><!-- --><?php if (boost_version(1,34,0)) { ?>

                <dt>
                <?php libref("Xpressive","doc/html/xpressive.html"); ?></dt>

                <dd>Regular expressions that can be written as strings or as
                expression templates, and which can refer to each other and
                themselves recursively with the power of context-free
                grammars, from Eric Niebler.</dd><?php } ?>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div id="sidebar">
        <?php virtual("/common/sidebar-common.html"); ?><?php virtual("/common/sidebar-doc.html"); ?>
      </div>

      <div class="clear"></div>
    </div>
  </div>

  <div id="footer">
    <div id="footer-left">
      <div id="revised">
        <p>Revised $Date$</p>
      </div>

      <div id="copyright">
        <p>Copyright Beman Dawes, David Abrahams, 1998-2005.</p>

        <p>Copyright Rene Rivera 2004-2005.</p>
      </div><?php virtual("/common/footer-license.html"); ?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html"); ?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
