<?php

namespace Jtl\Connector\Vivino\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Jtl\Connector\Vivino\Application as JTLApplication;
use App\Models\Option;

class Options {

    private static $cache = [];

    public static function get($name, $default = null ) {
        if ( ! isset(static::$cache[$name]) ) {
            try {

                $option = JTLApplication::query("SELECT * FROM options WHERE name = ?",[$name])->fetch(\PDO::FETCH_OBJ);

                if ( 'int' === $option->type ) {
                    static::$cache[$name] = (int) $option->value;
                } else if ( 'float' === $option->type ) {
                    static::$cache[$name] = (float) $option->value;
                } else if ( 'boolean' === $option->type ) {
                    static::$cache[$name] = (bool) $option->value;
                } else if ( 'json' === $option->type ) {
                    static::$cache[$name] = json_decode( $option->value );
                } else {
                    static::$cache[$name] = $option->value;
                }
            } catch ( ModelNotFoundException $err ) {
                static::$cache[$name] = $default;
            }
        }
        return static::$cache[$name];
    }
}
