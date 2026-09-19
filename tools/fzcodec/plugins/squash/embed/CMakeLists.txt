# Static libfzcodec for the Squash in-tree plugin (PIC, system zstd/brotli/zlib).
enable_language(CXX)
set(FZCODEC_ROOT "${CMAKE_CURRENT_SOURCE_DIR}/../../..")

find_package(Threads REQUIRED)
find_package(ZLIB REQUIRED)
find_package(PkgConfig REQUIRED)
pkg_check_modules(ZSTD REQUIRED libzstd)
pkg_check_modules(BROTLIENC REQUIRED libbrotlienc)
pkg_check_modules(BROTLIDEC REQUIRED libbrotlidec)

add_library(fzcodec_embed STATIC
	${FZCODEC_ROOT}/src/fzcodec.c
	${FZCODEC_ROOT}/src/fz_sniff.c
	${FZCODEC_ROOT}/src/fz_tools.c
	${FZCODEC_ROOT}/src/fz_zpaq.cpp
	${FZCODEC_ROOT}/third_party/libzpaq/libzpaq.cpp)
set_property(TARGET fzcodec_embed PROPERTY POSITION_INDEPENDENT_CODE ON)
set_property(TARGET fzcodec_embed PROPERTY C_STANDARD 11)
set_property(TARGET fzcodec_embed PROPERTY CXX_STANDARD 17)
target_compile_definitions(fzcodec_embed PRIVATE unix)
target_include_directories(fzcodec_embed
	PUBLIC
		${FZCODEC_ROOT}/include
		${ZSTD_INCLUDE_DIRS}
		${BROTLIENC_INCLUDE_DIRS}
		${BROTLIDEC_INCLUDE_DIRS}
	PRIVATE
		${FZCODEC_ROOT}/src
		${FZCODEC_ROOT}/third_party/libzpaq)
target_link_libraries(fzcodec_embed
	PUBLIC
		${ZSTD_LIBRARIES}
		${BROTLIENC_LIBRARIES}
		${BROTLIDEC_LIBRARIES}
		ZLIB::ZLIB
		Threads::Threads)
set(FZCODEC_EMBED_TARGET fzcodec_embed PARENT_SCOPE)
