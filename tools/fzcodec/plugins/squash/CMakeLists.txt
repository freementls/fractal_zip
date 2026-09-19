# Two modes:
#  1. Squash source tree (SQUASH_VERSION_API set): squash_plugin() drop-in.
#  2. Standalone: pkg-config squash-0.8 → libsquash0.8-plugin-fzcodec.so
set(FZCODEC_ROOT "${CMAKE_CURRENT_SOURCE_DIR}/../..")

if (DEFINED SQUASH_VERSION_API)
	include(SquashPlugin)
	squash_plugin(
		NAME fzcodec
		SOURCES squash-fzcodec.c
		EMBED_SUBDIR embed
		EMBED_TARGET_VAR FZCODEC_EMBED_TARGET
		INCLUDE_DIRS ${FZCODEC_ROOT}/include
		C_STANDARD c11)
	return()
endif()

if (NOT TARGET fzcodec)
	message(FATAL_ERROR "standalone plugin build expects the fzcodec target from the parent")
endif()

find_package(PkgConfig REQUIRED)
pkg_check_modules(SQUASH REQUIRED squash-0.8)

add_library(squash0.8-plugin-fzcodec SHARED squash-fzcodec.c)
set_target_properties(squash0.8-plugin-fzcodec PROPERTIES
	OUTPUT_NAME squash0.8-plugin-fzcodec
	PREFIX lib
	C_STANDARD 11)
target_include_directories(squash0.8-plugin-fzcodec PRIVATE
	${FZCODEC_ROOT}/include
	${SQUASH_INCLUDE_DIRS})
target_link_libraries(squash0.8-plugin-fzcodec PRIVATE fzcodec ${SQUASH_LIBRARIES})
target_link_directories(squash0.8-plugin-fzcodec PRIVATE ${SQUASH_LIBRARY_DIRS})

set(PLUGIN_OUT "${CMAKE_BINARY_DIR}/squash-plugins/fzcodec")
add_custom_command(TARGET squash0.8-plugin-fzcodec POST_BUILD
	COMMAND ${CMAKE_COMMAND} -E make_directory "${PLUGIN_OUT}"
	COMMAND ${CMAKE_COMMAND} -E copy $<TARGET_FILE:squash0.8-plugin-fzcodec> "${PLUGIN_OUT}/"
	COMMAND ${CMAKE_COMMAND} -E copy "${CMAKE_CURRENT_SOURCE_DIR}/squash.ini" "${PLUGIN_OUT}/squash.ini")
