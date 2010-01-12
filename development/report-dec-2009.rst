
==============================================
Review Wizard Status Report for December 2009
==============================================

News
====

Polygon Library Accepted

Boost 1.40 Released
  New Libraries: None
  Revised Libraries: Accumulators, Asio, Circular Buffer, Filesystem, Foreach, Function, Fusion, Hash, Interprocess, Intrusive, MPL, Program Options, Proto, Python, Serialization, Unordered, Xpressive

Geometry Library Accepted

Boost 1.41 Released
  New Libraries: Property Tree
  Revised Libraries: DateTime, Filesystem, Iostreams, Math, Multi-index Containers, Proto, Python, Regex, Spirit, System, Thread, Unordered, Utility, Wave, Xpressive

MSM Library Review Underway

Constrained Value Review - Review Result still Pending



Older Issues
============

The Time Series Library, accepted in August 2007, has not yet been
submitted to SVN.

The Floating Point Utilities Library, has not yet been submitted to
SVN.   It is slated to be integrated with the Boost.Math library.

The Switch Library, accepted provisionally in January 2008,
has not yet been submitted for mini-review and full acceptance.

The Phoenix Library, accepted provisionally in September 2008,
has not yet been submitted for mini-review and full acceptance.

For libraries that are still waiting to get into SVN, please get them
ready and into the repository. The developers did some great work
making the libraries, so don't miss the chance to share that work with
others.


General Announcements
=====================

As always, we need experienced review managers.   The review queue has
been growing substantially but we have had few volunteers, so manage
reviews if possible and if not please make sure to watch the review
schedule and participate. Please take a look at the list of libraries
in need of managers and check out their descriptions. In general
review managers are active boost participants or library
contributors. If you can serve as review manager for any of them,
email Ron Garcia or John Phillips, "garcia at osl dot iu dot edu"
and "phillips at mps dot ohio-state dot edu" respectively.

We are also suffering from a lack of reviewers. While we all
understand time pressures and the need to complete paying work, the
strength of Boost is based on the detailed and informed reviews
submitted by you. A recent effort is trying to secure at least five
people who promise to submit reviews as a precondition to starting
the review period. Consider volunteering for this and even taking the
time to create the review as early as possible. No rule says you can
only work on a review during the review period.

A link to this report will be posted to www.boost.org. If you would
like us to make any modifications or additions to this report before
we do that, please email Ron or John.

If you're a library author and plan on submitting a library for review
in the next 3-6 months, send Ron or John a short description of your
library and we'll add it to the Libraries Under Construction below. We
know that there are many libraries that are near completion, but we
have hard time keeping track all of them. Please keep us informed
about your progress.

The included review queue isn't a classic queue. It is more an unordered list of the libraries awaiting review. As such, any library in the queue can be reviewed once the developer is ready and a review manager works with the wizards and the developer to schedule a review. It is not FIFO.


Review Queue
============

* Lexer
* Shifted Pointer
* Logging
* Log
* Join
* Pimpl
* Task
* Endian
* Conversion
* Sorting
* GIL.IO
* AutoBuffer
* String Convert
* Move
* Containers
* Interval Containers
* Type Traits Extensions
* Interthreads
* Bitfield
* Lockfree

--------------------


Lexer
-----
:Author: Ben Hanson

:Review Manager: Eric Neibler

:Download: `Boost Vault <http://boost-consulting.com/vault/index.php?action=downloadfile&filename=boost.lexer.zip&directory=Strings%20-%20Text%20Processing>`__

:Description:
    A programmable lexical analyser generator inspired by 'flex'.
    Like flex, it is programmed by the use of regular expressions
    and outputs a state machine as a number of DFAs utilising
    equivalence classes for compression.


Shifted Pointer
---------------
:Author: Phil Bouchard

:Review Manager: Needed

:Download: `Boost Vault <http://www.boost-consulting.com/vault/index.php?&direction=0&order=&directory=Memory>`__

:Description:
  Smart pointers are in general optimized for a specific resource
  (memory usage, CPU cycles, user friendliness, ...)   depending on
  what the user need to make the most of.   The purpose of this smart
  pointer is mainly to allocate the reference counter (or owner) and
  the object itself at the same time so that dynamic memory management
  is simplified thus accelerated and cheaper on the memory map.


Logging
-------
:Author: John Torjo

:Review Manager: Gennadiy Rozental

:Download: http://torjo.com/log2/

:Description: Used properly, logging is a very powerful tool. Besides aiding
    debugging/testing, it can also show you how your application is
    used. The Boost Logging Library allows just for that, supporting
    a lot of scenarios, ranging from very simple (dumping all to one
    destination), to very complex (multiple logs, some enabled/some
    not, levels, etc).   It features a very simple and flexible
    interface, efficient filtering of messages, thread-safety,
    formatters and destinations, easy manipulation of logs, finding
    the best logger/filter classes based on your application's
    needs, you can define your own macros and much more!


Log
---
:Author: Andrey Semashev

:Review Manager: Needed

:Download: `Boost Vault <http://tinyurl.com/cm9lum>`__

:Description: The library is aimed to help adding logging features to
  applications. It provides out-of-box support for many widely used
  capabilities, such as formatting and filtering based on attributes,
  sending logs to a syslog server or to Windows Event Log, or simply
  storing logs into files. It also provides basic support for the
  library initialization from a settings file. The library can also be
  used for a wider range of tasks and implement gathering and processing
  statistical information or notifying user about application events.


Join
----
:Author: Yigong Liu

:Review Manager: Needed

:Download: http://channel.sourceforge.net/

:Description: Join is an asynchronous, message based C++ concurrency
  library based on join calculus. It is applicable both to
  multi-threaded applications and to the orchestration of asynchronous,
  event-based applications. It follows Comega's design and
  implementation and builds with Boost facilities. It provides a high
  level concurrency API with asynchronous methods, synchronous methods,
  and chords which are "join-patterns" defining the synchronization,
  asynchrony, and concurrency.


Pimpl
-----
:Author: Vladimir Batov

:Review Manager: Needed

:Download: | `Boost Vault <http://www.boost-consulting.com/vault/index.php?action=downloadfile&filename=Pimpl.zip&directory=&>`__
                    | http://www.ddj.com/cpp/205918714 (documentation)

:Description: The Pimpl idiom is a simple yet robust technique to
  minimize coupling via the separation of interface and implementation
  and then implementation hiding.   This library provides a convenient
  yet flexible and generic deployment technique for the Pimpl idiom.
  It's seemingly complete and broadly applicable, yet minimal, simple
  and pleasant to use.


Task
----

:Author: Oliver Kowalke

:Review Manager: Needed

:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=boost-threadpool.2.tar.gz&amp;directory=Concurrent%20Programming>`__

:Description:

 Formerly called Thread Pool
 The library provides:
 * thread creation policies: 

   * determines the management of worker threads:
   * fixed set of threads in pool
   * create workerthreads on demand (depending on context)
   * let worker threads ime out after certain idle time

 * channel policies: manages access to queued tasks:
       * bounded channel with high and low watermark for queuing tasks
       * unbounded channel with unlimited number of queued tasks
       * rendezvous syncron hand-over between producer and consumer threads

 * queueing policy: determines how tasks will be removed from channel:
       * FIFO
       * LIFO
       * priority queue (attribute assigned to task)
       * smart insertions and extractions (for instance remove oldest task with 
         certain attribute by newest one)
 * tasks can be chained and lazy submit of taks is also supported (thanks to
   Braddocks future library).
 * returns a task object from the submit function. The task it self can
   be interrupted if its is cooperative (means it has some interruption points
   in its code -> ``this_thread::interruption_point()`` ).


Endian
------
:Author: Beman Dawes

:Review Manager: Needed

:Download: http://mysite.verizon.net/beman/endian-0.10.zip

:Description:


Conversion
----------
:Author: Vicente Botet

:Review Manager: Needed

:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=conversion.zip&amp;directory=Utilities&amp;>`__

:Description:

 Generic explicit conversion between unrelated types.

 Boost.Conversion provides:
     * a generic ``convert_to`` function which can be specialized by the user to
       make explicit conversion between unrelated types.
     * a generic ``assign_to`` function which can be specialized by the user to
       make explicit assignation between unrelated types.
     * conversion between ``std::complex`` of explicitly convertible types.
     * conversion between ``std::pair`` of explicitly convertible types.
     * conversion between ``boost::optional`` of explicitly convertible types.
     * conversion between ``boost::rational`` of explicitly convertible types.
     * conversion between ``boost::interval`` of explicitly convertible types.
     * conversion between ``boost::chrono::time_point`` and ``boost::ptime``.
     * conversion between ``boost::chrono::duration`` and ``boost::time_duration``.


Sorting
-------
:Author: Steven Ross

:Review Manager: Needed

:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=algorithm_sorting.zip>`__

:Description:

 A grouping of 3 templated hybrid radix/comparison-based sorting
 algorithms that provide superior worst-case and average-case
 performance to std::sort: integer_sort, which sorts fixed-size data
 types that support a rightshift (default of >>) and a comparison
 (default of <) operator.   float_sort, which sorts standard
 floating-point numbers by safely casting them to integers.
 string_sort, which sorts variable-length data types, and is optimized
 for 8-bit character strings.

 All 3 algorithms have O(n(k/s + s)) runtime where k is the number of
 bits in the data type and s is a constant, and limited memory overhead
 (in the kB for realistic inputs).   In testing, integer_sort varies
 from 35% faster to 8X as fast as std::sort, depending on processor,
 compiler optimizations, and data distribution.   float_sort is roughly
 7X as fast as std::sort on x86 processors.   string_sort is roughly 2X
 as fast as std::sort.


GIL.IO
------
:Author: Christian Henning

:Review Manager: Needed

:Download: `GIL Google Code Vault <http://gil-contributions.googlecode.com/files/rc2.zip>`__

:Description: I/O extension for boost::gil which allows reading and
  writing of/in various image formats ( tiff, jpeg, png, etc ). This
  review will also include the Toolbox extension which adds some common
  functionality to gil, such as new color spaces, algorithms, etc.



AutoBuffer
----------
:Author: Thorsten Ottosen

:Review Manager: Needed

:Download: `Here <http://www.cs.aau.dk/~nesotto/boost/auto_buffer.zip>`__

:Description:
  Boost.AutoBuffer provides a container for efficient dynamic, local buffers.
  Furthermore, the container may be used as an alternative to std::vector,
  offering greater flexibility and sometimes better performance.



String Convert
--------------
:Author: Vladimir Batov

:Review Manager: Needed

:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=boost-string-convert.zip>`__

:Description:

 The library takes the approach of boost::lexical_cast in the area of
 string-to-type and type-to-string conversions, builds on the past
 boost::lexical_cast experience and advances that conversion
 functionality further to additionally provide:

  * throwing and non-throwing conversion-failure behavior;
  * support for the default value to be returned when conversion fails;
  * two types of the conversion-failure check -- basic and better/safe;
  * formatting support based on the standard I/O Streams and the standard
    (or user-defined) I/O Stream-based manipulators
    (like std::hex, std::scientific, etc.);
  * locale support;
  * support for boost::range-compliant char and wchar_t-based string containers;
  * no DefaultConstructibility requirement for the Target type;
  * consistent framework to uniformly incorporate any type-to-type conversions.

 It is an essential tool with applications making extensive use of
 configuration files or having to process/prepare considerable amounts
 of data in, say, XML, etc.


Move
----------------
:Author: Ion Gaztanaga

:Review Manager: Needed

:Download: http://svn.boost.org/svn/boost/sandbox/move/ and online documentation at http://svn.boost.org/svn/boost/sandbox/move/libs/move/doc/html/index.html

:Description:

 In C++0x, move semantics are implemented with the introduction of
 rvalue references. They allow us to implement move() without verbosity
 or runtime overhead. Boost.Move is a library that offers tools to
 implement those move semantics not only in compilers with rvalue
 references but also in compilers conforming to C++03.


Containers
----------
:Author: Ion Gaztanaga

:Review Manager: Needed

:Download: http://www.boostpro.com/vault/index.php?action=downloadfile&filename=boost.move.container.zip&directory=Containers&

:Documentation: http://svn.boost.org/svn/boost/sandbox/move/libs/container/doc/html/index.html

:Description:

 Boost.Container library implements several well-known containers,
 including STL containers. The aim of the library is to offers advanced
 features not present in standard containers or to offer the latest
 standard draft features for compilers that comply with C++03.


Interval Containers Library
---------------------------
:Author: Joachim Faulhaber

:Download: http://www.boostpro.com/vault/index.php?action=downloadfile&filename=itl_3_2_0.zip&directory=Containers

:Documentation: http://herold-faulhaber.de/boost_itl/doc/libs/itl/doc/html/index.html

:Review Manager: Needed

:Description:

 The Interval Template Library (Itl) provides intervals
 and two kinds of interval containers: Interval_sets and
 interval_maps. Interval_sets and maps can be used just
 as sets or maps of elements. Yet they are much more
 space and time efficient when the elements occur in
 contiguous chunks: intervals. This is obviously the case
 in many problem domains, particularly in fields that deal
 with problems related to date and time.

 Interval containers allow for intersection with interval_sets
 to work with segmentation. For instance you might want
 to intersect an interval container with a grid of months
 and then iterate over those months.

 Finally interval_maps provide aggregation on
 associated values, if added intervals overlap with
 intervals that are stored in the interval_map. This
 feature is called aggregate on overlap. It is shown by
 example:

 ::

     typedef set<string> guests;
     interval_map<time, guests> party;
     guests mary; mary.insert("Mary");
     guests harry; harry.insert("Harry");
     party += make_pair(interval<time>::rightopen(20:00, 22:00),mary);
     party += make_pair(interval<time>::rightopen_(21:00, 23:00),harry);
     // party now contains
     [20:00, 21:00)->{"Mary"}
     [21:00, 22:00)->{"Harry","Mary"} //guest sets aggregated on overlap
     [22:00, 23:00)->{"Harry"}

 As can be seen from the example an interval_map has both
 a decompositional behavior (on the time dimension) as well as
 a accumulative one (on the associated values).


Type Traits Extensions
--------------------------
:Author: Frederic Bron

:Review Manager: Needed

:Download: http://svn.boost.org/trac/boost/browser/sandbox/type_traits

:Description:

 The purpose of the addition is to add type traits to detect if types T and U
 are comparable in the sense of <, <=, >, >=, == or != operators, i.e. if
 t<u has a sens when t is of type T and u of type U (same for <=, >, >=, ==,
 !=).

 The following traits are added:

 is_equal_to_comparable<T,U>
 is_greater_comparable<T,U>
 is_greater_equal_comparable<T,U>
 is_less_comparable<T,U>
 is_less_equal_comparable<T,U>
 is_not_equal_to_comparable<T,U>

 The names are based on the corresponding names of the standard
 template library (<functional> header, section 20.3.3 of the
 standard).

 The code has the following properties:
 * returns true if t<u is meaningful and returns a value convertible to bool
 * returns false if t<u is meaningless.
 * fails with compile time error if t<u is meaningful and returns void
 (a possibility to avoid compile time error would be to return true
 with an operator, trick but this has little sens as returning false
 would be better)


InterThreads
-------------------
:Author: Vicente J. Botet Escriba

:Review Manager: Needed

:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=interthreads.zip&amp;directory=Concurrent%20Programming&amp;>`__

:Description:

 Boost.InterThreads extends Boost.Threads adding some features:
       * thread decorator: thread_decorator allows to define
	 setup/cleanup functions which will be called only once by
	 thread: setup before the thread function and cleanup at thread
	 exit.
       * thread specific shared pointer: this is an extension of the
	 thread_specific_ptr providing access to this thread specific
	 context from other threads. As it is shared the stored pointer
	 is a shared_ptr instead of a raw one.
       * thread keep alive mechanism: this mechanism allows to detect
	 threads that do not prove that they are alive by calling to the
	 keep_alive_point regularly. When a thread is declared dead a
	 user provided function is called, which by default will abort
	 the program.
       * thread tuple: defines a thread groupe where the number of
	 threads is know statically and the threads are created at
	 construction time.
       * set_once: a synchonizer that allows to set a variable only once,
	 notifying to the variable value to whatever is waiting for that.
       * thread_tuple_once: an extension of the boost::thread_tuple which
	 allows to join the thread finishing the first, using for that
	 the set_once synchronizer.
       * thread_group_once: an extension of the boost::thread_group which
	 allows to join the thread finishing the first, using for that
	 the set_once synchronizer.


 (thread_decorator and thread_specific_shared_ptr) are based on the
 original implementation of threadalert written by Roland Schwarz.

 Boost.InterThreads extends Boost.Threads adding thread setup/cleanup
 decorator, thread specific shared pointer, thread keep alive
 mechanism and thread tuples.


Bitfield
---------------
:Author: Vicente J. Botet Escriba

:Review Manager: Needed

:Download: http://svn.boost.org/svn/boost/sandbox/bitfield with documentation available at http://svn.boost.org/svn/boost/sandbox/bitfield/libs/integer/doc/index.html

:Description:

 Portable bitfields traits. Boost.Bitfield consists of:
  * a generic bitfield traits class providing generic getter and setter methods.
  * a BOOST_BITFIELD_DCL macro making easier the definition of the bitfield traits and the bitfield getter and setter functions.


Lockfree
------------------
:Author: Tim Blechmann

:Review Manager: Needed

:Download: http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=boost_lockfree-241109.zip&amp;directory=Concurrent%20Programming&amp;

:Documentation: http://tim.klingt.org/boost_lockfree/

:Description:

 boost.lockfree provides implementations of lock-free data structures.
 lock-free data structures can be accessed by multiple threads without
 the necessity of blocking synchronization primitives such as guards.
 lock-free data structures can be used in real-time systems, where
 blocking algorithms may lead to high worst-case execution times, to
 avoid priority inversion, or to increase the scalability for
 multi-processor machines.

 boost.lockfree provides:
  * boost::lockfree::fifo, a lock-free fifo queue
  * boost::lockfree::stack, a lock-free stack

 the code is available from from my personal git repository:
  * git://tim.klingt.org/boost_lockfree.git
  * http://tim.klingt.org/git?p=boost_lockfree.git



Libraries under development
===========================

Persistent
----------

:Author: Tim Blechmann

:Description:

 A library, based on Boost.Serialization, that provides access to persistent 
 objects with an interface as close as possible to accessing regular objects 
 in memory.

 * object ownership concepts equivalent to the ones used by Boost.SmartPtr: 
   shared, weak, scoped (and raw)
 * ACID transactions, including recovery after a crash and "Serializable" 
   isolation level
 * concurrent transactions, nested transactions, distributed transactions 
 * concurrent access containers: STL containers whose nodes are implemented as 
   persistent objects and can be accessed without moving the container to 
   memory. Concurrent transactions modifying the container are only repeated in 
   the rare cases the same container node is changed simultanisouly by 2 
   threads.
 * extensible by other transactional resources, e.g. an object relational 
   mapper based on the upcoming Boost.Rdb library. Multiple resources can be 
   combined to one database, with distributed transactions among them.


 Please let us know of any libraries you are currently
 developing that you intend to submit for review.


See http://svn.boost.org/trac/boost/wiki/LibrariesUnderConstruction
for a current listing of libraries under development.
