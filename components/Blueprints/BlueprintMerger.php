<?php
namespace WordPress\Blueprints;

use InvalidArgumentException;

class BlueprintMerger {
    /**
     * Merge multiple Blueprint arrays into a single array.
     * This implements a simplified version of the merge algorithm
     * described in the Blueprints v2 specification.
     *
     * @param array<int,array> $blueprints Array of Blueprint arrays
     * @return array The merged Blueprint
     */
    public static function merge( array $blueprints ): array {
        if ( empty( $blueprints ) ) {
            throw new InvalidArgumentException( 'No blueprints provided for merge.' );
        }

        $target = [ 'version' => $blueprints[0]['version'] ?? 2 ];

        foreach ( $blueprints as $bp ) {
            if ( ! isset( $bp['version'] ) ) {
                throw new InvalidArgumentException( 'All blueprints must declare version.' );
            }
            if ( $bp['version'] !== $target['version'] ) {
                throw new InvalidArgumentException( 'Blueprint version mismatch during merge.' );
            }

            // Simple scalar properties
            foreach ( [ 'siteLanguage', 'activeTheme' ] as $prop ) {
                if ( isset( $bp[$prop] ) ) {
                    if ( ! isset( $target[$prop] ) ) {
                        $target[$prop] = $bp[$prop];
                    } elseif ( $target[$prop] !== $bp[$prop] ) {
                        throw new InvalidArgumentException( "Conflict on $prop" );
                    }
                }
            }

            // Merge associative arrays
            foreach ( [ 'constants', 'siteOptions', 'postTypes', 'fonts' ] as $prop ) {
                if ( isset( $bp[$prop] ) && is_array( $bp[$prop] ) ) {
                    if ( ! isset( $target[$prop] ) ) {
                        $target[$prop] = [];
                    }
                    foreach ( $bp[$prop] as $k => $v ) {
                        if ( isset( $target[$prop][$k] ) && $target[$prop][$k] !== $v ) {
                            throw new InvalidArgumentException( "Conflict in $prop for key $k" );
                        }
                        $target[$prop][$k] = $v;
                    }
                }
            }

            // Append lists
            foreach ( [ 'steps', 'content', 'media' ] as $prop ) {
                if ( isset( $bp[$prop] ) && is_array( $bp[$prop] ) ) {
                    if ( ! isset( $target[$prop] ) ) {
                        $target[$prop] = [];
                    }
                    $target[$prop] = array_merge( $target[$prop], $bp[$prop] );
                }
            }

            // Merge plugins/themes/muPlugins by identifier
            foreach ( [ 'plugins', 'themes', 'muPlugins' ] as $prop ) {
                if ( isset( $bp[$prop] ) && is_array( $bp[$prop] ) ) {
                    if ( ! isset( $target[$prop] ) ) {
                        $target[$prop] = [];
                    }
                    foreach ( $bp[$prop] as $item ) {
                        $slug = is_string( $item ) ? $item : ( $item['source'] ?? json_encode( $item ) );
                        $exists = false;
                        foreach ( $target[$prop] as $existing ) {
                            $existingSlug = is_string( $existing ) ? $existing : ( $existing['source'] ?? json_encode( $existing ) );
                            if ( $existingSlug === $slug ) {
                                if ( $existing != $item ) {
                                    throw new InvalidArgumentException( "Conflict in $prop for $slug" );
                                }
                                $exists = true;
                                break;
                            }
                        }
                        if ( ! $exists ) {
                            $target[$prop][] = $item;
                        }
                    }
                }
            }

            // Merge users
            if ( isset( $bp['users'] ) && is_array( $bp['users'] ) ) {
                if ( ! isset( $target['users'] ) ) {
                    $target['users'] = [];
                }
                foreach ( $bp['users'] as $user ) {
                    $found = false;
                    foreach ( $target['users'] as &$existing ) {
                        if ( $existing['username'] === $user['username'] || $existing['email'] === $user['email'] ) {
                            if ( $existing['username'] !== $user['username'] || $existing['email'] !== $user['email'] || ( $existing['role'] ?? null ) !== ( $user['role'] ?? null ) ) {
                                throw new InvalidArgumentException( 'User conflict on ' . $user['username'] );
                            }
                            if ( isset( $user['meta'] ) ) {
                                if ( ! isset( $existing['meta'] ) ) {
                                    $existing['meta'] = [];
                                }
                                foreach ( $user['meta'] as $k => $v ) {
                                    if ( isset( $existing['meta'][$k] ) && $existing['meta'][$k] !== $v ) {
                                        throw new InvalidArgumentException( 'User meta conflict for ' . $user['username'] );
                                    }
                                    $existing['meta'][$k] = $v;
                                }
                            }
                            $found = true;
                            break;
                        }
                    }
                    unset( $existing );
                    if ( ! $found ) {
                        $target['users'][] = $user;
                    }
                }
            }

            // Merge roles
            if ( isset( $bp['roles'] ) && is_array( $bp['roles'] ) ) {
                if ( ! isset( $target['roles'] ) ) {
                    $target['roles'] = [];
                }
                foreach ( $bp['roles'] as $role ) {
                    $found = false;
                    foreach ( $target['roles'] as &$existing ) {
                        if ( $existing['name'] === $role['name'] ) {
                            if ( isset( $role['capabilities'] ) ) {
                                if ( ! isset( $existing['capabilities'] ) ) {
                                    $existing['capabilities'] = [];
                                }
                                foreach ( $role['capabilities'] as $cap => $val ) {
                                    if ( isset( $existing['capabilities'][$cap] ) && $existing['capabilities'][$cap] !== $val ) {
                                        throw new InvalidArgumentException( 'Role capability conflict for ' . $role['name'] );
                                    }
                                    $existing['capabilities'][$cap] = $val;
                                }
                            }
                            $found = true;
                            break;
                        }
                    }
                    unset( $existing );
                    if ( ! $found ) {
                        $target['roles'][] = $role;
                    }
                }
            }
        }

        return $target;
    }
}
