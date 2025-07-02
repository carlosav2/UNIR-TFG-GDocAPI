<?php
// TagService.php: Clase del servicio para tareas sobre tags
// Carlos Ahumada Vidal

namespace GDocAPI\Services;

use GDocAPI\Controllers\TagController;

class TagService
{
    /**
	 * Verifica si un tag específico existe en el conjunto de tags obtenidos
     * 
     * @param string $tag con el nombre del tag 
     * @return bool true si existe; false si no existe
     */
    static function existeTag($tag)
    {
        $tags_array = TagController::obtenerTodosTags(false); // Obtiene todos los tags.
        $tag_nombre = $tag; // Extrae el nombre del tag a buscar.

        foreach ($tags_array as $item) {
            if ($item->tag == $tag_nombre) { // Compara el nombre del tag con los elementos existentes.
                return true; // El tag existe.
            }
        }
        return false; // El tag no se encontró.
    }


    /**
	 * Busca un tag en un array de tags y devuelve el objeto correspondiente si lo encuentra
     * 
     * @param string $tag con el nombre del tag a buscar
     * @param array $tags_array con el array de tags donde buscar
     * @return bool true si existe; false si no existe
     */
    static function tagEnArray($tag, $tags_array)
    {
        foreach ($tags_array as $item) {
            if ($item->tag == $tag) { // Compara el nombre del tag con los elementos del array.
                return $item; // Devuelve el objeto del tag encontrado.
            }
        }
        return false; // El tag no está en el array.
    }
}