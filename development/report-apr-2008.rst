==========================================
Review Wizard Status Report for April 2008
==========================================

News
====

December 7 - Forward Library Accepted - Awaiting SVN

December 16 - Unordered Containers Library Accepted - In SVN

December 21 - Factory Library Accepted - Awaiting SVN

January 13 - Switch Library Accepted Provisionally - Awaiting submission for
mini review

January 18 - Singleton Library Rejected - Awaiting resubmission, John Torjo
has already volunteered to manage the next review

January 30 - Flyweight Library Accepted - Awaiting SVN

February 13 - Logging Library Rejected - Awaiting resubmission for new
review, John Torjo has already resubmitted and Gennadiy Rozental has again
volunteered to manage the review

February 27 - Floating Point Utilities Library Accepted - Awaiting SVN

March 14 - Proto Library Accepted - Exists as a component in xpressive, but
not yet as a separate library

April 20 - Egg review ongoing


Older Issues
============

The Scope Exit review report has not been submitted. John Phillips will step
in as substitute review manager and produce a report

The binary_int library, accepted in October 2005 has not yet been submitted
to SVN. The authors are strongly encouraged to contact the review wizards

The Accumulators library, accepted in February 2007 has not yet been
submitted to SVN

The Quantitative Units library, accepted in April 2007 has not yet been
submitted to SVN

The Globally Unique Identifier library, accepted provisionally in May 2007
has not yet been submitted for mini-review and full acceptance

The Time Series Library, accepted in August 2007 has not yet been submitted
to SVN

The Exception library, accepted in October 2007 is in SVN

For libraries that are still waiting to get into SVN, please get them
ready and into the repository. The developers did some great work
making the libraries, so don't miss the chance to share that work with
others. Also notice that the review process page has been updated with
a section on rights and responsibilities of library submitters. 

For the Scope Exit review, we would like to publicly apologize to Alexander
Nasonov for how long this has languished without a report. The review
wizards will work to make sure this doesn't happen any more.


General Announcements
=====================

As always, we need experienced review managers. In the past few months there
have been a large number of reviews, but the flow of high quality
submissions is just as big, so manage reviews if possible and if not please
make sure to watch the review schedule and participate. Please take a look
at the list of libraries in need of managers and check out their
descriptions. In general review managers are active boost participants or
library contributors. If you can serve as review manager for any of them,
email Ron Garcia or John Phillips, "garcia at cs dot indiana dot edu" and
"phillips at mps dot ohio-state dot edu" respectively.

A link to this report will be posted to www.boost.org. If you would like us
to make any modifications or additions to this report before we do that,
please email Ron or John.

If you're a library author and plan on submitting a library for review in the
next 3-6 months, send Ron or John a short description of your library and
we'll add it to the Libraries Under Construction below. We know that there
are many libraries that are near completion, but we have hard time keeping
track all of them. Please keep us informed about your progress.

Review Queue
============

* Finite State Machines
* Property Map (fast-track)
* Graph (fast-track) 
* Lexer    
* Thread-Safe Signals
* Boost.Range (Update)
* Shifted Pointer
* DataFlow Signals
* Logging
* Futures

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


Lexer
-----
:Author: Ben Hanson

:Review Manager: Need Volunteer 

:Download: `Boost Sandbox Vault <http://boost-consulting.com/vault/index.php?action=downloadfile&filename=boost.lexer.zip&directory=Strings%20-%20Text%20Processing&>`__

:Description:

  A programmable lexical analyser generator inspired by 'flex'.
  Like flex, it is programmed by the use of regular expressions
  and outputs a state machine as a number of DFAs utilising
  equivalence classes for compression.


Thread-Safe Signals
-------------------
:Author: Frank Hess

:Review Manager: Need Volunteer 

:Download: `Boost Sandbox Vault <http://www.boost-consulting.com/vault/index.php?&direction=0&order=&directory=thread_safe_signals>`__

:Description: A thread-safe implementation of Boost.Signals that
  has some interface changes to accommodate thread safety, mostly with
  respect to automatic connection management.


Boost.Range (Update)
--------------------
:Author: Neil Groves

:Review Manager: Needed

:Download: `Boost Sandbox Vault <http://www.boost-consulting.com/vault/index.php?action=downloadfile&filename=range_ex.zip&directory=>`__

:Description: A significant update of the range library, including
 range adapters.

Shifted Pointer
---------------
:Author: Phil Bouchard

:Review Manager: Needed

:Download: `Boost Sandbox Vault <http://www.boost-consulting.com/vault/index.php?&direction=0&order=&directory=Memory>`__

:Description: Smart pointers are in general optimized for a specific
 resource (memory usage, CPU cycles, user friendliness, ...) depending
 on what the user need to make the most of.  The purpose of this smart
 pointer is mainly to allocate the reference counter (or owner) and
 the object itself at the same time so that dynamic memory management
 is simplified thus accelerated and cheaper on the memory map.


DataFlow Signals
----------------
:Author: Stjepan Rajko

:Review Manager: Needed

:Download: http://dancinghacker.com/code/dataflow/

:Description: Dataflow is a generic library for dataflow programming.
 Dataflow programs can typically be expressed as a graph in which vertices
 represent components that process data, and edges represent the flow of data
 between the components. As such, dataflow programs can be easily
 reconfigured by changing the components and/or the connections.


Logging
-------
:Author: John Torjo

:Review Manager: Gennadiy Rozental

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


Futures
-------
:Author: Braddock Gaskill

:Review Manager: Needed

:Download: http://braddock.com/~braddock/future/

:Description: The goal of the boost.future library is to provide a definitive
 future implementation with the best features of the numerous
 implementations, proposals, and academic papers floating around, in the
 hopes to avoid multiple incompatible future implementations in libraries of
 related concepts (coroutines, active objects, asio, etc). This library hopes
 to explore the combined implementation of the best future concepts.









