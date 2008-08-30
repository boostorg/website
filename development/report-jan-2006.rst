============================================
Review Wizard Status Report for January 2006 
============================================


News 
====

Happy New Year!  Here are some statistics regarding Boost Library
reviews in 2005:

 * 12 Libraries were reviewed
 * 8 Libraries were accepted 
 * 1 Library (Function Types) was accepted pending a mini-review
 * 2 Libraries were rejected
 * 1 Library has yet to receive a final verdict (ASIO)


Policy Pointer has been removed from the review queue because the author has
stated that it is not quite ready.  

We need review managers.  Please take a look at the list of libraries
in need of managers and check out their descriptions.  If you can
serve as review manager for any of them, send one of us an email.


Note: 
 If you have any suggestions about how we could improve 
 the Review Wizard's status report, 
 please email "reportbase at gmail dot com" 
 and "garcia at cs dot indiana dot edu". 


Review Managers Needed 
======================

There are a few libraries in the review queue in need
of review managers. If you would like to volunteer to be a review
manager, please contact Ron or Tom.

The following libraries still require review managers: 

 * Fusion
 * Shmem
 * Pimpl Pointer
 * Type Traits (modification)
 * Function Types



Review Queue
============

 * Fixed Strings - January 19 2006 - January 28 2006

 * Intrusive Containers
 * Function Types (mini-re-review)
 * Shmem
 * Fusion
 * Pimpl Pointer
 * Type Traits (modification)

--------------------

Fixed Strings 
-------------
   :Author: Reece Dunn 
   :Review Manager: Harmut Kaiser

   :Download:
     Boost Sandbox (http://boost-sandbox.sourceforge.net/) under fixed_string

   :Description: 
     The fixed string library provides buffer overrun protection for static 
     sized strings (char s[ n ]). It provides a C-style string 
     interface for compatibility with C code (for 
     example, porting a C program to C++). 
     There is also a std::string-style interface using a class based on 
     flex_string by Andre Alexandrescu with a few limitations due to the 
     non-resizable nature of the class. 


Intrusive Containers
--------------------
   :Author: Olaf Krzikalla
   :Review Manager: to be determined

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


Function Types (mini-re-review)
-------------------------------
    :Author: Tobias Schwinger
    :Review Manager: to be determined

    :Download:
      http://boost-sandbox.sourceforge.net/vault/ 

    :Description:
     This library provides a metaprogramming facility 
      to classify, decompose and synthesize function-, 
      function pointer-, function reference- and 
      member function pointer types. For the purpose 
      of this documentation, these types are 
      collectively referred to as function 
      types (this differs from the standard 
      definition and redefines the term from 
      a programmer's perspective to refer to 
      the most common types that involve functions). 

     The classes introduced by this library 
      shall conform to the concepts of the 
      Boost Metaprogramming library (MPL). 

     The Function Types library enables the user to: 
      * test an arbitrary type for 
	being a function type of specified kind, 
      * inspect properties of function types, 
      * view and modify sub types of an 
	encapsulated function type with 
	MPL Sequence operations, and 
      * synthesize function types. 

     This library supports variadic functions and 
      can be configured to support 
      non-default calling conventions. 


Shmem
-----
   :Author: Ion Gaztanaga
   :Review Manager: to be determined

   :Download:
     Boost Sandbox Vault -> Memory (http://boost-sandbox.sourceforge.net/vault/index.php?direction=0&order=&directory=Memory)

     http://ice.prohosting.com/newfunk/boost/libs/shmem/doc/html/index.html

   :Description:
     Shmem offers tools to simplify shared memory usage in
     applications. These include shared memory creation/destruction and
     synchronization objects. It also implements dynamic allocation of
     portions of a shared memory segment and an easy way to construct C++
     objects in shared memory.

     Apart from this, Shmem implements a wide range of STL-like containers
     and allocators that can be safely placed in shared memory, helpful to
     implement complex shared memory data-bases and other efficient
     inter-process communications.


Fusion
------
   :Author: Joel de Guzman
   :Review Manager: to be determined

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
    :Review Manager: to be determined

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


Type_Traits (modification)
--------------------------
    :Author: Alexander Nasonov
    :Review Manager: to be determined

    :Download:
      http://cpp-experiment.sourceforge.net/promote-20050917.tar.gz
      or http://cpp-experiment.sourceforge.net/promote-20050917/

    :Description:
      Proposal to add promote, integral_promotion and
      floating_point_promotion class templates to type_traits library.

      Alexander tried it on different compilers with various success:
      GNU/Linux (gentoo-hardened): gcc 3.3 and 3.4, Intel 7, 8 and 9
      Windows: VC7 free compiler
      Sparc Solaris: Sun C++ 5.3 and 5.7

      See comments at the beginning of promote_enum_test.cpp for what is broken.
      http://cpp-experiment.sourceforge.net/promote-20050917/libs/type_traits/test/promote_enum_test.cpp

      Alexander requests a fast-track review.
        
 


Libraries under development 
===========================

Property Tree
-------------
   :Author: Marcin Kalicinski
    
   :Download:
     Boost Sandbox Vault (http://boost-consulting.com/vault/)
     property_tree_rev3.zip


Please let us know of any libraries you are currently
developing that you intend to submit for review.
   
    
