==============================================
Review Wizard Status Report for June 2009
==============================================

News
====

Futures: Williams variant Accepted; Gaskill variant Rejected

Boost 1.38 Released
  New Libraries:
  Revised Libraries:

Boost.Range Extension Accepted

Polynomial Library Rejected

Boost 1.39 Released

Constrained Value Review - Review Result Pending



Library Issues
==============

The Time Series Library, accepted in August 2007, has not yet been
submitted to SVN.  Eric Niebler and John Phillips are working on
making the changes suggested during the review.

The Floating Point Utilities Library, has not yet been submitted to
SVN.  It is slated to be integrated with the Boost.Math library.

The Switch Library, accepted provisionally in January 2008,
has not yet been submitted for mini-review and full acceptance.

The Phoenix Library, accepted provisionally in September 2008, has not
yet been submitted for mini-review and full acceptance.  A rewrite of
Phoenix, basing it on the Proto metaprogramming library, has just
begun.

Maintenance of The Property Tree Library has been taken over by
Sebastian Redl from Marcin Kalicinski.  The library has been checked
into svn trunk, but Sebastian is doing major maintenance on it in a
branch.  He is aiming for a 1.41 or 1.40 release.



General Announcements
=====================

As always, we need experienced review managers.  The review queue has
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


Review Queue
============

* Lexer
* Shifted Pointer
* Logging
* Log
* Join
* Pimpl
* Thread Pool
* Endian
* Meta State Machine
* Conversion
* Sorting
* GIL.IO
* AutoBuffer
* String Convert

--------------------


Lexer
-----
:Author: Ben Hanson

:Review Manager: Eric Niebler

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
  (memory usage, CPU cycles, user friendliness, ...)  depending on
  what the user need to make the most of.  The purpose of this smart
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
   not, levels, etc).  It features a very simple and flexible
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
  and then implementation hiding.  This library provides a convenient
  yet flexible and generic deployment technique for the Pimpl idiom.
  It's seemingly complete and broadly applicable, yet minimal, simple
  and pleasant to use.


Thread Pool
-----------

:Author: Oliver Kowalke

:Review Manager: Needed

:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=boost-threadpool.2.tar.gz&amp;directory=Concurrent%20Programming>`__

:Description:
  The library provides:
    - thread creation policies: determines the management of worker threads:
       - fixed set of threads in pool
       - create workerthreads on demand (depending on context)
       - let worker threads ime out after certain idle time
    - channel policies: manages access to queued tasks:
       - bounded channel with high and low watermark for queuing tasks
       - unbounded channel with unlimited number of queued tasks
       - rendezvous syncron hand-over between producer and consumer threads
    - queueing policy: determines how tasks will be removed from channel:
       - FIFO
       - LIFO
       - priority queue (attribute assigned to task)
       - smart insertions and extractions (for instance remove oldest task with          certain attribute by newst one)
    - tasks can be chained and lazy submit of taks is also supported (thanks to
      Braddocks future library).
    - returns a task object from the submit function. The task it self can
      be interrupted if its is cooperative (means it has some interruption points
      in its code -> ``this_thread::interruption_point()`` ).


Endian
------
:Author: Beman Dawes

:Review Manager: Needed

:Download: http://mysite.verizon.net/beman/endian-0.10.zip

:Description: 
  Header boost/integer/endian.hpp provides integer-like byte-holder
  binary types with explicit control over byte order, value type, size,
  and alignment. Typedefs provide easy-to-use names for common
  configurations.

  These types provide portable byte-holders for integer data,
  independent of particular computer architectures. Use cases almost
  always involve I/O, either via files or network connections. Although
  data portability is the primary motivation, these integer byte-holders
  may also be used to reduce memory use, file size, or network activity
  since they provide binary integer sizes not otherwise available.


Meta State Machine
------------------
:Author: Christophe Henry

:Review Manager: Needed

:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?direction=0&amp;order=&amp;directory=Msm>`__

:Description:  Msm is a framework which enables you to build a Finite State Machine
  in a straightforward, descriptive and easy-to-use manner . It requires
  minimal effort to generate a working program from an UML state machine
  diagram. This work was inspired by the state machine described in the
  book of David Abrahams and Aleksey Gurtovoy "C++ Template
  Metaprogramming" and adds most of what UML Designers are expecting
  from an UML State Machine framework:

  * Entry and Exit Methods
  * Guard Conditions
  * Sub state machines (also called composite states in UML)
  * History
  * Terminate Pseudo-State
  * Deferred Events
  * Orthogonal zones
  * Explicit entry into sub state machine states
  * Fork
  * Entry / Exit pseudo states
  * Conflicting transitions


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
  (default of <) operator.  float_sort, which sorts standard
  floating-point numbers by safely casting them to integers.
  string_sort, which sorts variable-length data types, and is optimized
  for 8-bit character strings.

  All 3 algorithms have O(n(k/s + s)) runtime where k is the number of
  bits in the data type and s is a constant, and limited memory overhead
  (in the kB for realistic inputs).  In testing, integer_sort varies
  from 35% faster to 8X as fast as std::sort, depending on processor,
  compiler optimizations, and data distribution.  float_sort is roughly
  7X as fast as std::sort on x86 processors.  string_sort is roughly 2X
  as fast as std::sort.


GIL.IO
------
:Author: Christian Henning

:Review Manager: Needed

:Download: `GIL Google Code Vault <http://gil-contributions.googlecode.com/files/rc2.zip>`__

:Description: I/O extension for ``boost::gil`` which allows reading and
  writing of/in various image formats ( tiff, jpeg, png, etc ). This
  review will also include the Toolbox extension which adds some common
  functionality to gil, such as new color spaces, algorithms, etc.



AutoBuffer
----------
:Author: Thorsten Ottosen

:Review Manager: Robert Stewart

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


Libraries under development
===========================


Please let us know of any libraries you are currently
developing that you intend to submit for review.


Mirror
------
:Author: Matus Chochlik

:Download: | http://svn.boost.org/svn/boost/sandbox/mirror/doc/index.html
	   | `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=mirror.zip>`__

:Description:

 The aim of the Mirror library is to provide useful meta-data at both
 compile-time and run-time about common C++ constructs like namespaces,
 types, typedef-ined types, classes and their base classes and member
 attributes, instances, etc. and to provide generic interfaces for
 their introspection.

 Mirror is designed with the principle of stratification in mind and
 tries to be as less intrusive as possible. New or existing classes do
 not need to be designed to directly support Mirror and no Mirror
 related code is necessary in the class' definition, as far as some
 general guidelines are followed

 Most important features of the Mirror library that are currently
 implemented include:

    * Namespace-name inspection.

    * Inspection of the whole scope in which a namespace is defined

    * Type-name querying, with the support for typedef-ined typenames
      and typenames of derived types like pointers, references,
      cv-qualified types, arrays, functions and template names. Names
      with or without nested-name-specifiers can be queried.

    * Inspection of the scope in which a type has been defined

    * Uniform and generic inspection of class' base classes.  One can
      inspect traits of the base classes for example their types,
      whether they are inherited virtually or not and the access
      specifier (private, protected, public).

    * Uniform and generic inspection of class' member attributes. At
      compile-time the count of class' attributes and their types,
      storage class specifiers (static, mutable) and some other traits
      can be queried. At run-time one can uniformly query the names
      and/or values (when given an instance of the reflected class) of
      the member attributes and sequentially execute a custom functor
      on every attribute of a class.

    * Traversals of a class' (or generally type's) structure with user
      defined visitors, which are optionally working on an provided
      instance of the type or just on it's structure without any
      run-time data. These visitors are guided by Mirror through the
      structure of the class and optionally provided with contextual
      information about the current position in the traversal.

 I'm hoping to have it review ready in the next few months.


Interval Template Library
-------------------------
:Author: Joachim Faulhaber 

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

StlConstantTimeSize
-------------------
:Author: Vicente J. Botet Escriba
 
:Download: `Boost Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&filename=constant_time_size.zip&amp;directory=Containers&amp;>`__
 
:Description:
 
 Boost.StlConstantTimeSize Defines a wrapper to the stl container list
 giving the user the chioice for the complexity of the size function:
 linear time, constant time or quasi-constant.  In future versions the
 library could include a similar wrapper to slist.
 

InterThreads
------------
:Author: Vicente J. Botet Escriba
 
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


Channel
-------
:Author: Yigong Liu

:Download: http://channel.sourceforge.net

:Description:
  Channel is a C++ template library to provide name spaces for distributed
  message passing and event dispatching. Message senders and receivers bind to
  names in name space; binding and matching rules decide which senders will
  bind to which receivers (the binding-set); then message passing could happen
  among bound senders and receivers.

  The type of name space is a template parameter of Channel. Various name
  spaces (linear/hierarchical/associative) can be used for different
  applications. For example, integer ids can be used to send messages in
  linear name space, string path name ids (such as "/sports/basketball") can
  be used to send messages in hierarchical name space and regex patterns or
  Linda tuple-space style tuples can be used to send messages in associative
  name space.

  Dispatcher is another configurable template parameter of Channel; which
  dispatch messages/events from senders to bounded receivers. The design of
  dispatchers can vary in several dimensions:
  how msgs move: push or pull;
  how callbacks executed: synchronous or asynchronous.
  Sample dispatchers includes : synchronous broadcast dispatcher, asynchronous
  dispatchers with choice_arbiter and join_arbiters.

  Name space and dispatchers are orthogonal; they can mix and match together
  freely. Name spaces and name-binding create binding-sets for sender and
  receiver, and dispatchers are algorithms defined over the binding-set.

  Distributed channels can be connected to allow transparent distributed
  message passing. Filters and translators are used to control name space
  changes.


Bitfield
--------
:Authot: Vicente Botet

:Download:

:Description:

I have adapted the Bitfield library from Emile Cormier with its
permision and I would like you add it to the libraries under
developement list. The library is quite stable but I want to add some
test with Boost.Endian before adding it to the formal review schedule
list.
 
Boost.Bitfield consists of:
 * a generic bitfield traits class providing generic getter and setter methods.
 * a BOOST_BITFIELD_DCL macro making easier the definition of the
   bitfield traits and the bitfield getter and setter functions::

    struct X {
        typedef boost::ubig_32 storage_type;
        storage_type d0;
        typedef unsigned int value_type;
        BOOST_BITFIELD_DCL(storage_type, d0, unsigned int, d00, 0, 10);
        BOOST_BITFIELD_DCL(storage_type, d0, unsigned int, d01, 11, 31);
    };


Synchro
-------
:Author: Vicente Botet
 
:Download: `Boost Vault: <http://www.boostpro.com/vault/index.php?action=downloadfile&filename=synchro.zip&directory=Concurrent%20Programming&>`__
  `Boost Sandbox: <https://svn.boost.org/svn/boost/sandbox/synchro>`__
  Html doc included only on the Vault
 
:Description: Synchro provides:
 
* A uniform usage of Boost.Thread and Boost.Interprocess
  synchronization mechanisms based on lockables(mutexes) concepts and
  locker(guards) concepts.

    * lockables traits and lock generators, 
    * generic free functions on lockables as: `lock`, `try_lock`, ... 
    * locker adapters of the Boost.Thread and Boost.Interprocess lockers models,
    * complete them with the corresponding models for single-threaded
      programms: `null_mutex` and `null_condition` classes,
    * locking families,
    * `semaphore` and `binary_semaphore`, 
    * `condition_lockable` lock which put toghether a lock and its
      associated conditions.
 
* A coherent exception based timed lock approach for functions and constructors,
 
* A rich palete of lockers as

    * `strict_locker`, `nested_strict_locker`,
    * `condition_locker`,
    * `reverse_locker`, `nested_reverse_locker`,
    * `locking_ptr`, `on_derreference_locking_ptr`,
    * `externally_locked`,
    
* `array_unique_locker` on multiple lockables.
 
* Generic free functions on multiple lockables `lock`, `try_lock`,
  `lock_until`, `lock_for`, `try_lock_until`, `try_lock_for`, `unlock`

* lock adapters of the Boost.Thread and Boost.Interprocess lockable models,

* `lock_until`, `lock_for`, `try_lock_until`, `try_lock_for`
 
* A polymorphic lockable hierarchy.
 
* High-level abstractions for handling more complicated
  synchronization problems, including

    * `monitor` for guaranteeing exclusive access to an object.
 
* A rendezvous mechanism for handling direct communication between
  objects `concurrent_components` via `ports` using an
  accept-synchronize protocol based on the design of the concurrency
  library in the Beta language.
 
* Language-like Synchronized Block Macros
 
