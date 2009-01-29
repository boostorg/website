==============================================
Review Wizard Status Report for November 2008
==============================================

News
====


May 7 - Scope Exit Library Accepted - Awaiting SVN

May 17 - Egg Library Rejected

August 14 - Boost 1.36 Released
   New Libraries: Accumulators, Exception, Units, Unordered Containers

August 27 - Finite State Machines Rejected

September 10 - Data Flow Signals Rejected

September 30 - Phoenix Accepted Conditionally

November 3 - Boost 1.37 Released
   New Library: Proto

November 10 - Thread-Safe Signals Accepted - Awaiting SVN

November 25 - Globally Unique Identifier Library mini-Review in progress


Older Issues
============

The Quantitative Units library, accepted in April 2007 is in SVN  
(listed as units).

The Time Series Library, accepted in August 2007, has not yet been  
submitted
to SVN.

The Switch Library, accepted provisionally in January 2008,
has not yet been submitted for mini-review and full acceptance.

Property Map (Fast-Track) and Graph (Fast-Track) have been removed
from the review queue.  The author (Andrew Sutton) intends to submit a
new version of this work at a later time.


A few libraries have been reviewed and accepted into boost, but have
not yet appeared in SVN as far as I can tell.  Could some light be
shed on the status of the following libraries? Apologies if I have
simply overlooked any of them:


* Flyweight (Joaquin Ma Lopez Munoz)
* Floating Point Utilities (Johan Rade)
* Factory (Tobias Schwinger)
* Forward (Tobias Schwinger)
* Scope Exit (Alexander Nasonov)
* Time Series (Eric Niebler)
* Property Tree (Marcin Kalicinski) -- No documentation in SVN

Any information on the whereabouts of these libraries would be greatly
appreciated.



For libraries that are still waiting to get into SVN, please get them
ready and into the repository. The developers did some great work
making the libraries, so don't miss the chance to share that work with
others. Also notice that the review process page has been updated with
a section on rights and responsibilities of library submitters.



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
* Boost.Range (Update)
* Shifted Pointer
* Logging
* Futures - Williams
* Futures - Gaskill
* Join
* Pimpl
* Constrained Value
* Thread Pool
* Polynomial

--------------------


Lexer
-----
:Author: Ben Hanson

:Review Manager: Eric Neibler

:Download: `Boost Sandbox Vault <http://boost-consulting.com/vault/index.php?action=downloadfile&filename=boost.lexer.zip&directory=Strings%20-%20Text%20Processing>`__

:Description:
   A programmable lexical analyser generator inspired by 'flex'.
   Like flex, it is programmed by the use of regular expressions
   and outputs a state machine as a number of DFAs utilising
   equivalence classes for compression.


Boost.Range (Update)
--------------------
:Author: Neil Groves

:Review Manager: Needed

:Download: `Boost Sandbox Vault <http://www.boost-consulting.com/vault/index.php?action=downloadfile&filename=range_ex.zip>`__

:Description: A significant update of the range library, including
  range adapters.

Shifted Pointer
---------------
:Author: Phil Bouchard

:Review Manager: Needed

:Download: `Boost Sandbox Vault <http://www.boost-consulting.com/vault/index.php?&direction=0&order=&directory=Memory>`__

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


Futures
-------
:Author: Braddock Gaskill

:Review Manager: Tom Brinkman

:Download: http://braddock.com/~braddock/future/

:Description: The goal of this library is to provide a definitive
  future implementation with the best features of the numerous
  implementations, proposals, and academic papers floating around, in
  the hopes to avoid multiple incompatible future implementations in
  libraries of related concepts (coroutines, active objects, asio,
  etc). This library hopes to explore the combined implementation of
  the best future concepts.


Futures
-------
:Author: Anthony Williams

:Review Manager: Tom Brinkman

:Download: | http://www.justsoftwaresolutions.co.uk/files/n2561_future.hpp 
             (code)
           | http://www.open-std.org/jtc1/sc22/wg21/docs/papers/2008/n2561.html
             (description)

:Description: This library proposes a kind of return buffer that takes
  a value (or an exception) in one (sub-)thread and provides the value
  in another (controlling) thread.  This buffer provides essentially
  two interfaces:

  * an interface to assign a value as class promise and
  * an interface to wait for, query and retrieve the value (or exception)
    from the buffer as classes unique_future and shared_future.  While a
    unique_future provides move semantics where the value (or exception)
    can be retrieved only once, the shared_future provides copy semantics
    where the value can be retrieved arbitrarily often.

  A typical procedure for working with promises and futures looks like:

  * control thread creates a promise,
  * control thread gets associated future from promise,
  * control thread starts sub-thread,
  * sub-thread calls actual function and assigns the return value to
    the promise,
  * control thread waits for future to become ready,
  * control thread retrieves value from future.

  Also proposed is a packaged_task that wraps one callable object and
  provides another one that can be started in its own thread and assigns
  the return value (or exception) to a return buffer that can be
  accessed through one of the future classes.

  With a packaged_task a typical procedure looks like:

  * control thread creates a packaged_task with a callable object,
  * control thread gets associated future from packaged_task,
  * control thread starts sub-thread, which invokes the packaged_task,
  * packaged_task calls the callable function and assigns the return value,
  * control thread waits for future to become ready,
  * control thread retrieves value from future.


Notice that we are in the unusual position of having two very
different libraries with the same goal in the queue at the same
time. The Review Wizards would appreciate a discussion of the best way
to hold these two reviews to produce the best possible addition to
Boost.


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

:Download: | `Boost Sandbox Vault <http://www.boost-consulting.com/vault/index.php?action=downloadfile&filename=Pimpl.zip&directory=&>`__
           | http://www.ddj.com/cpp/205918714 (documentation)

:Description: The Pimpl idiom is a simple yet robust technique to
  minimize coupling via the separation of interface and implementation
  and then implementation hiding.  This library provides a convenient
  yet flexible and generic deployment technique for the Pimpl idiom.
  It's seemingly complete and broadly applicable, yet minimal, simple
  and pleasant to use.



Constrained Value
-----------------
:Author: Robert Kawulak

:Review Manager: Jeff Garland

:Download: http://rk.go.pl/f/constrained_value.zip

:Description:

  The Boost Constrained Value library contains class templates useful
  for creating constrained objects. A simple example is an object
  representing an hour of a day, for which only integers from the range
  [0, 23] are valid values:

  ::

      bounded_int<int, 0, 23>::type hour;
      hour = 20; // OK
      hour = 26; // exception!

  Behavior in case of assignment of an invalid value can be customized. For
  instance, instead of throwing an exception as in the example above, the value
  may be adjusted to meet the constraint:

  ::

      wrapping_int<int, 0, 255>::type buffer_index;
      buffer_index = 257; // OK: wraps the value to fit in the range
      assert( buffer_index == 1 );

  The library doesn't focus only on bounded objects as in the examples above --
  virtually any constraint can be imposed by using a predicate:

  ::

      // constraint (a predicate)
      struct is_odd {
	 bool operator () (int i) const
	 { return (i % 2) != 0; }
      };

  ::

      // and the usage is as simple as:
      constrained<int, is_odd> odd_int = 1;
      odd_int += 2; // OK
      ++odd_int; // exception!

  The library has a policy-based design to allow for flexibility in defining
  constraints and behavior in case of assignment of invalid values. Policies may
  be configured at compile-time for maximum efficiency or may be changeable at
  runtime if such dynamic functionality is needed.


Thread Pool
-----------

:Author: Oliver Kowalke

:Review Manager: Needed

:Download: `Boost Sandbox Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=boost-threadpool.2.tar.gz&amp;directory=Concurrent%20Programming>`__

:Description:
  The library provides:

  - thread creation policies: determines the management of worker threads
      - fixed set of threads in pool
      - create workerthreads on demand (depending on context)
      - let worker threads ime out after certain idle time

  - channel policies: manages access to queued tasks
     - bounded channel with high and low watermark for queuing tasks
     - unbounded channel with unlimited number of queued tasks
     - rendezvous syncron hand-over between producer and consumer threads

  - queueing policy: determines how tasks will be removed from channel
     - FIFO
     - LIFO
     - priority queue (attribute assigned to task)
     - smart insertions and extractions (for instance remove oldest task with
       certain attribute by newst one)

  - tasks can be chained and lazy submit of taks is also supported (thanks to
    Braddocks future library).

  - returns a task object from the submit function. The task it self can
    be interrupted if its is cooperative (means it has some interruption points
    in its code -> ``this_thread::interruption_point()`` ).



Polynomial
----------
:Author: Pawel Kieliszczyk

:Review Manager: Needed

:Download: `Boost Sandbox Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&amp;filename=polynomial.zip>`__

:Description:
  The library was written to enable fast and faithful polynomial manipulation.
  It provides:

  - main arithmetic operators (+, -, * using FFT, /, %),
  - gcd,
  - different methods of evaluation (Horner Scheme, Compensated Horner
    Algorithm, by preconditioning),
  - derivatives and integrals,
  - interpolation,
  - conversions between various polynomial forms (special functions for
    creating Chebyshev, Hermite, Laguerre and Legendre form).





Libraries under development
===========================


Please let us know of any libraries you are currently
developing that you intend to submit for review.


Logging
-------
:Author: Andrey Semashev

:Download: http://boost-log.sourceforge.net

:Description:
 I am working on a logging library, online docs available here:
 The functionality is quite ready, the docs are at about 70% ready. There
 are a few examples, but no tests yet (I'm using the examples for
 testing). I hope to submit it for a review at early 2009.


Mirror
------
:Author: Matus Chochlik

:Download: | http://svn.boost.org/svn/boost/sandbox/mirror/doc/index.html
	   | `Boost Sandbox Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&filename=mirror.zip>`__

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
 
:Download: `Boost Sandbox Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&filename=constant_time_size.zip&directory=Containers&>`__
 
:Description:
 
 Boost.StlConstantTimeSize Defines a wrapper to the stl container list
 giving the user the chioice for the complexity of the size function:
 linear time, constant time or quasi-constant.  In future versions the
 library could include a similar wrapper to slist.
 

InterThreads
-------------------
:Author: Vicente J. Botet Escriba
 
:Download: | `Boost Sandbox Vault <http://www.boostpro.com/vault/index.php?action=downloadfile&filename=interthreads.zip&directory=Concurrent%20Programming&>`__
 | `Boost Sandbox <https://svn.boost.org/svn/boost/sandbox/interthreads>`__
 | Html doc included only on the Vault
 
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
