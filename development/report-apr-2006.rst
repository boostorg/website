============================================
Review Wizard Status Report for April 2006
============================================

News
====

April 1, 2006 -- The "Promotion Traits" Review Begins (Fast-Track)
Proposal to add promote, integral_promotion and
floating_point_promotion class templates to type_traits library.

April 6, 2006 -- The "Function Types" Review Begins (Fast-Track)
This library provides a metaprogramming facility
to classify, decompose and synthesize function-, function pointer-,
function reference- and member function pointer types.

March 22, 2006 -- Asio Accepted
Announcement: http://lists.boost.org/Archives/boost/2006/03/102287.php

February 17, 2006 - Shared Memory Library Accepted
Announcement: http://lists.boost.org/boost-announce/2006/02/0083.php

February 5, 2006 - Fixed String Library Rejected
Announcement: http://lists.boost.org/boost-announce/2006/02/0081.php

We need experienced review managers.  Please take a look at
the list of libraries in need of managers and check out their
descriptions.  If you can serve as review manager for any of
them, email Ron Garcia or Tom Brinkman "garcia at cs dot indiana dot edu"
and "reportbase at gmail dot com" respectively.

A link to this report will be posted to www.boost.org.
If you would like us to make any modifications or additions to this
report before we do that, please email Ron or Tom.

If you're library author and plan on submitting a library for review
in the next 3-6 months, send Ron or Tom a
short description of your library and we'll add it to the
Libraries Under Construction below.  We know that there are many
libaries that are near completion, but we have hard time keeping
track all of them. Please keep us informed about your progress.

Review Queue
============

 * Promotion Traits - April 1, 2006 (fast-track)
 * Function Types - April 6, 2006 (fast-track)
 * Fusion
 * Pimpl Pointer
 * Property Tree
 * Physical Quantities System
 * Intrusive Containers

--------------------

Function Types (mini-re-review)
-------------------------------
    :Author: Tobias Schwinger
    :Review Manager: Tom Brinkman

    :Download:
      http://boost-sandbox.sourceforge.net/vault/

    :Description:
      This library provides a metaprogramming facility to classify,
      decompose and synthesize function-, function pointer-, function
      reference- and member function pointer types. For the purpose of
      this documentation, these types are collectively referred to as
      function types (this differs from the standard definition and
      redefines the term from a programmer's perspective to refer to
      the most common types that involve functions).

      The classes introduced by this library shall conform to the
      concepts of the Boost Metaprogramming library (MPL).

      The Function Types library enables the user to:
       * test an arbitrary type for being a function type of specified kind,
       * inspect properties of function types,
       * view and modify sub types of an encapsulated function type with
	 MPL Sequence operations, and
       * synthesize function types.

      This library supports variadic functions and can be configured
      to support non-default calling conventions.


Promotion Traits
----------------
    :Author: Alexander Nasonov
    :Review Manager: Tobias Schwinger

    :Download:
      http://cpp-experiment.sourceforge.net/promote-20050917.tar.gz

    :Description:
      Proposal to add promote, integral_promotion and
      floating_point_promotion class templates to type_traits library.

      Alexander tried it on different compilers with various success:
      GNU/Linux (gentoo-hardened): gcc 3.3 and 3.4, Intel 7, 8 and 9
      Windows: VC7 free compiler
      Sparc Solaris: Sun C++ 5.3 and 5.7

      See comments at the beginning of
      promote_enum_test.cpp for what is broken.


Intrusive Containers
--------------------
   :Author: Olaf Krzikalla
   :Review Manager: Thorsten Ottosen

   :Download:
     http://people.freenet.de/turtle++/intrusive.zip

   :Description:
     While intrusive containers were and are widely used in C, they became
     more and more forgotten in the C++-world due to the presence of the
     standard containers, which don't support intrusive
     techniques. Boost.Intrusive not only reintroduces this technique to
     C++, but also encapsulates the implementation in STL-like
     interfaces. Hence anyone familiar with standard containers can use
     intrusive containers with ease.


Fusion
------
   :Author: Joel de Guzman
   :Review Manager: Ron Garcia

   :Download:
     http://spirit.sourceforge.net/dl_more/fusion_v2/
     http://spirit.sourceforge.net/dl_more/fusion_v2.zip

   :Description:
     Fusion is a library of heterogenous containers and views and
     algorithms. A set of heterogenous containers (vector, list, set and
     map) is provided out of the box along with view classes that present
     various composable views over the data. The containers and views
     follow a common sequence concept with an underlying iterator concept
     that binds it all together, suitably making the algorithms fully
     generic over all sequence types.

     The architecture is somewhat modeled after MPL which in turn is
     modeled after STL. It is code-named "fusion" because the library is
     the "fusion" of compile time metaprogramming with runtime programming.


Pimpl Pointer
-------------
    :Author: Asger Mangaard
    :Review Manager: Need Volunteer

    :Download:
      Boost Sandbox (http://boost-consulting.com/vault/) under pimpl_ptr.

    :Description:
      The pimpl idiom is widely used to reduce compile times and disable
      code coupling. It does so by moving private parts of a class from the
      .hpp file to the .cpp file.
      However, it's implementation can be tricky, and with many pitfalls
      (especially regarding memory management).
      The pimpl_ptr library is a single header file, implementing a special
      policy based smart pointer to greately ease the implementation of the
      pimpl idiom.


Property Tree
-------------
   :Author: Marcin Kalicinski
   :Review Manager: Need Volunteer

   :Download:
     Boost Sandbox Vault - property_tree_rev4.zip
     http://kaalus.atspace.com/ptree

   :Description:
     Property tree is a data structure - a tree of (key, value)
     pairs. It differs from its cousin, "usual" property map, because
     it is hierarchical, not linear. Thus, it is more like a
     minimalistic Document Object Model, but not bound to any
     specific file format. It can store contents of XML files,
     windows registry, JSON files, INI files, even command line
     parameters.  The library contains parsers for all these formats,
     and more.


Physical Quantities System
--------------------------
   :Author: Andy Little
   :Review Manager: Need Volunteer

   :Download:
     http://tinyurl.com/7m5l8

   :Description:
      PQS (Physical Quantities System) is used for modelling
      physical-quantities in C++ programs. The advantages over using
      built-in types in the role include: trapping errors in
      dimensional analysis, detailed semantic specifications for
      reliable and repeatable conversions between units and
      self-documentation of source code. PQS is based around the
      principles and guidelines of the International System of Units
      (SI). The library predefines a large number of quantities,
      physical and maths constants using a common syntax. The library
      also includes (or will soon include) classes for manipulating
      quantities algebraically, for example angles (radians,
      steradians, degrees,minutes,seconds) and vectors, matrices and
      quaternions for more advanced modelling of physical systems.

Libraries under development
===========================

Geometry Library - Author - Andy Little (?)

C2_functions Library - Author - Marcus Mendenhall

Please let us know of any libraries you are currently
developing that you intend to submit for review.
