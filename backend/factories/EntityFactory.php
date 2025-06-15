<?php
/**
 * Entity Factory
 * Implements Factory Pattern for creating entity instances
 */
class EntityFactory {
    public static function create($entityType, $data = null) {
        switch (strtolower($entityType)) {
            case 'user':
                $entity = new User();
                break;
            case 'task':
                $entity = new Task();
                break;
            case 'recipe':
                $entity = new Recipe();
                break;
            case 'grocerylist':
                $entity = new GroceryList();
                break;
            default:
                throw new InvalidArgumentException("Unknown entity type: {$entityType}");
        }
        
        // If data is provided, populate the entity
        if ($data && is_array($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($entity, $key)) {
                    $entity->$key = $value;
                }
            }
        }
        
        return $entity;
    }
    
    public static function createFromDatabase($entityType, $dbData) {
        $entity = self::create($entityType);
        
        if ($dbData && is_array($dbData)) {
            foreach ($dbData as $key => $value) {
                if (property_exists($entity, $key)) {
                    $entity->$key = $value;
                }
            }
        }
        
        return $entity;
    }
}
?>
