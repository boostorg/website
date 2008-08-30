==============================================
Review Wizard Status Report for November 2007
==============================================

News
====

November 7, 2007 - Exception Library Accepted
 Announcement:  http://lists.boost.org/boost-users/2007/11/31912.php

We need experienced review managers.  Please take a look at the list
of libraries in need of managers and check out their descriptions.  In
general review managers are active boost participants or library
contributors.  If you can serve as review manager for any of them,
email Ron Garcia or John Phillips, "garcia at cs dot indiana dot edu"
and "jphillip at capital dot edu" respectively.

A link to this report will be posted to www.boost.org.
If you would like us to make any modifications or additions to this
report before we do that, please email Ron or John.

If you're library author and plan on submitting a library for review
in the next 3-6 months, send Ron or John a short description of your
library and we'll add it to the Libraries Under Construction below.
We know that there are many libraries that are near completion, but we
have hard time keeping track all of them. Please keep us informed
about your progress.

Review Queue
============

* Finite State Machines
* Floating Point Utilities
* Switch
* Property Map (fast-track)
* Graph (fast-track)
* Forward (fast-track)
* Singleton (fast-track)
* Factory (fast-track)
* Lexer
* Thread-Safe Signals
* Logging
* Flyweight
* Unordered Containers

--------------------


Finite State Machines
---------------------
:Author: Andrey Semashev
:Review Manager: Martin Vuille
:Download: `Boost Sandbox Vault <http://tinyurl.com/yjozfn>`__ 

:Description:

  The Boost.FSM library is an implementation of FSM (stands for
  Finite State Machine) programming concept. The main goals of the
  library are:

  * Simplicity. It should be very simple to create state machines using
    this library.
  * Performance. The state machine infrastructure should not be
    very time and memory-consuming in order to be applicable in
    more use cases.
  * Extensibility. A developer may want to add more states to an
    existing state machine.  A developer should also be able to
    specify additional transitions and events for the machine with
    minimum modifications to the existing code.


Floating Point Utilities
------------------------
:Author: Johan Råde
:Review Manager: Need Volunteer
:Download:
  `Boost Sandbox Vault <http://boost-consulting.com/vault/index.php?directory=Math%20-%20Numerics>`__

:Description: The Floating Point Utilities library contains the following:

 * Floating point number classification functions: fpclassify, isfinite, 
   isinf, isnan, isnormal (Follows TR1)
 * Sign bit functions: signbit, copysign, changesign (Follows TR1)
 * Facets that format and parse infinity and NaN according to the C99 
   standard (These can be used for portable handling of infinity and NaN 
   in text streams).


Switch
------
:Author: Steven Watanabe
:Review Manager: Need Volunteer
:Download: 
  `Boost Sandbox Vault <http://boost-consulting.com/vault/index.php?action=downloadfile&filename=mcs_units_v0.7.1.zip&directory=Units>`__

:Description:
  The built in C/C++ switch statement is very efficient. Unfortunately,
  unlike a chained if/else construct there is no easy way to use it when
  the number of cases depends on a template parameter. The Switch library 
  addresses this issue.


Property Map (fast-track)
-------------------------
:Author: Andrew Sutton
:Review Manager: Jeremy Siek
:Download: http://svn.boost.org/svn/boost/sandbox/graph-v2
:Description:
  A number of additions and modifications to the Property Map Library, 
  including: 

  * A constant-valued property map, useful for naturally unweighted  
    graphs.
  * A noop-writing property map, useful when you have to provide an  
    argument, but just don't care about the output.
  * See 
    `ChangeLog <http://svn.boost.org/trac/boost/browser/sandbox/graph-v2/libs/property_map/ChangeLog>`__
    for details.


Graph (fast-track)
------------------
:Author: Andrew Sutton
:Review Manager: Jeremy Siek
:Download: http://svn.boost.org/svn/boost/sandbox/graph-v2
:Description:
 A number of additions and modifications to the Graph Library, 
 including: 

 * Two new graph classes (undirected and directed) which are intended  
   to make the library more approachable for new developers
 * A suite of graph measures including degree and closeness  
   centrality, mean geodesic distance, eccentricity, and clustering  
   coefficients.
 * An algorithm for visiting all cycles in a directed graph (Tiernan's  
   from 1970ish). It works for undirected graphs too, but reports cycles  
   twice (one for each direction).
 * An algorithm for visiting all the cliques a graph (Bron&Kerbosch).  
   Works for both directed and undirected.
 * Derived graph measures radius and diameter (from eccentricity) and  
   girth and circumference (from Tiernan), and clique number (from  
   Bron&Kerbosch).
 * An exterior_property class that helps hides some of the weirdness  
   with exterior properties.
 * runtime and compile-time tests for the new algorithms.
 * a substantial amount of documentation 
 * Graph cores, implemented by David Gleich (@Stanford University)
 * Deterministic graph generators - capable of creating or inducing  
   specific types of graphs over a vertex set (e.g., star graph, wheel  
   graph, prism graph, etc). There are several other specific types that  
   could be added to this, but I haven't had the time just yet.


Forward (fast-track)
--------------------
:Author: Tobias Schwinger

:Review Manager: John Torjo

:Download: http://boost-consulting.com/vault/index.php?&direction=0&order=&directory=X-Files 

:Description: A brute-force solution to the forwarding problem.

Singleton (fast-track)
----------------------
:Author: Tobias Schwinger

:Review Manager: John Torjo

:Download: http://boost-consulting.com/vault/index.php?&direction=0&order=&directory=X-Files

:Description: Three thread-safe Singleton templates with an
  easy-to-use interface.


Factory (fast-track)
--------------------
:Author: Tobias Schwinger

:Review Manager: John Torjo

:Download: http://boost-consulting.com/vault/index.php?&direction=0&order=&directory=X-Files

:Description: Generic factories. 


Lexer
-----
:Author: Ben Hanson

:Review Manager: Need Volunteer 

:Download: http://boost-consulting.com/vault/index.php?action=downloadfile&filename=boost.lexer.zip&directory=Strings%20-%20Text%20Processing&

:Description:

  A programmable lexical analyser generator inspired by 'flex'.
  Like flex, it is programmed by the use of regular expressions
  and outputs a state machine as a number of DFAs utilising
  equivalence classes for compression.


Thread-Safe Signals
-------------------
:Author: Frank Hess

:Review Manager: Need Volunteer 

:Download: http://www.boost-consulting.com/vault/index.php?&direction=0&order=&directory=thread_safe_signals

:Description: A thread-safe implementation of Boost.signals that
  has some interface changes to accommodate thread safety, mostly with
  respect to automatic connection management.


Logging
-------
:Author: John Torjo

:Review Manager: Need Volunteer 

:Download: http://torjo.com/log2/

:Description: 
  Used properly, logging is a very powerful tool. Besides aiding
  debugging/testing, it can also show you how your application is
  used. The Boost Logging Library allows just for that, supporting
  a lot of scenarios, ranging from very simple (dumping all to one
  destination), to very complex (multiple logs, some enabled/some
  not, levels, etc).  It features a very simple and flexible
  interface, efficient filtering of messages, thread-safety,
  formatters and destinations, easy manipulation of logs, finding
  the best logger/filter classes based on your application's
  needs, you can define your own macros and much more!


Flyweight
---------
:Author: Joaquín M López Muñoz

:Review Manager: Need Volunteer

:Download: http://www.boost-consulting.com/vault/index.php?action=downloadfile&filename=flyweight.zip&directory=Patterns

:Description: Flyweights are small-sized handle classes granting
  constant access to shared common data, thus allowing for the
  management of large amounts of entities within reasonable memory
  limits. Boost.Flyweight makes it easy to use this common
  programming idiom by providing the class template flyweight<T>,
  which acts as a drop-in replacement for const T.


Unordered Containers
--------------------
:Author: Daniel James

:Review Manager: Need Volunteer

:Download: http://www.boost-consulting.com/vault/index.php?action=downloadfile&filename=unordered.zip&directory=Containers

:Description: An implementation of the unordered containers specified
  in TR1, with most of the changes from the recent draft standards.



Libraries under development
===========================

Dataflow
--------
:Author: Stjepan Rajko

:Description:
  The Dataflow library provides generic support for data
  producers, consumers, and connections between the two.  It also
  provides layers for several specific dataflow mechanisms, namely
  Boost.Signals, VTK data/display pipelines, and plain
  pointers. The Dataflow library came out of the Signal Network
  GSoC project, mentored by Doug Gregor.

:Status:
  I am polishing the Dataflow library for submission, and am expecting
  to add it to the review queue in the next couple of months.  
  I am currently ironing out some faults in the design of the library,
  filling in missing features, and testing it on / adapting it to
  different dataflow mechanisms (currently VTK and soon
  Boost.Iostreams).  As soon as I'm pretty sure that things are going
  the right way, I'll submit this to the review queue while I do the
  finishing touches.


Constrained Value
-----------------
:Author: Robert Kawulak

:Download:
  http://rk.go.pl/f/constrained_value.zip

  http://rk.go.pl/r/constrained_value (Documentation)

:Description:
  The Constrained Value library contains class templates 
  useful for creating constrained objects. The simplest example 
  of a constrained object is hour. The only valid values for an hour 
  within a day are integers from the range [0, 23]. With this library, 
  you can create a variable which behaves exactly like int, but does 
  not allow for assignment of values which do not belong to the 
  allowed range. The library doesn't focus only on constrained 
  objects that hold a value belonging to a specified range (i.e., 
  bounded objects). Virtually any constraint can be imposed using 
  appropriate predicate. You can specify what happens in case of 
  assignment of an invalid value, e.g. an exception may be thrown or 
  the value may be adjusted to meet the constraint criterions.

:Status: I'm planning to finish it in 1-2 months.


Please let us know of any libraries you are currently
developing that you intend to submit for review.
