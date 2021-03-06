<?php
//show_source(basename($_SERVER['PHP_SELF'])); // DELETE THIS LINE BEFORE GOING PRODUCTION

/**
 * CLASS: ResourceControl
 *
 * @author Ben VanDenburg
 * @date 21 April 2010
 * @version 1.0 beta (21 April 2010)
 *
 * DESCRIPTION: This class uses semaphores to represent a shared resource that
 * requires exclusive access. You instantiate the class passing the name of the
 * resource you wish to have a locking mechanism on, and you can use the lock
 * and unlock methods accordingly. This class is currently beta because it has
 * been untested. 
 * 
 * This class was originally written for the Music and Gaming Festival's
 * (MAGFest) LAN room barcode inventory control system. 
 * 
 * This code is released to the general public under the GPL. 
 *
 * Copyright (C) 2010 Ben VanDenburg
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

 class ResourceControl
 {
    private $BASE_NAME;
    private $MAX_ACQUIRE;
    
    private $resourceName;
    private $resourceID; // numerical resource id obtained from 
    private $sem; // this is a resource identifier returned by sem_get()
    private $locked;
    
    /**
     * Default constructor. Accepts the resource name for which you wish
     * to have a locking mechanism on. This constructor will generate the 
     * resource ID used for obtaining the semaphore with sem_get. The resource
     * ID Is generated by concatinating the class constant BASE_NAME with the
     * supplied resource name, and then taking the absolute value of the 
     * string after running it through the crc32 hashing function. 
     *
     * This constructor will also obtain the semaphore with sem_get using the
     * generated resourceID.
     *
     * @param $resourceName the name or ID of your resource. 
     *
     * @throws RuntimeException in the event that this class is unable to get
     *         a semaphore using sem_get.
     */
    public function __construct($resourceName, 
                                $baseName = '234223', 
                                $maxAcquire = 1)
    {
        $this->BASE_NAME = $baseName;
        $this->MAX_ACQUIRE = $maxAcquire;
        $this->resourceName = $resourceName;
        $this->resourceID = abs(crc32($this->BASE_NAME . $this->resourceName));
        $this->sem = sem_get($this->resourceID, $this->MAX_ACQUIRE, 0600, true);
        
        if($this->sem === false)
        {
            // Something went terribly wrong, you can not use this instance of
            // the class.
            throw new RuntimeException("Unable to obtain semaphore using " .
                "resource ID " . $this->resourceID . " for resource " . 
                $this->resourceName . "!");
        }
        
        $this->locked = false;
    }
    
    /**
     * This method will issue a lock request on the semaphore for the 
     * resource represented by this class. This method WILL BLOCK (if necessary)
     * until the lock can be acquired. IF YOU CALL THIS METHOD TWICE FOR THE
     * SAME RESOURCE AND THAT RESOURCE WAS ALREADY LOCKED BY THIS CLASS, THIS
     * METHOD WILL BLOCK FOREVER!
     *
     * @see http://www.php.net/manual/en/function.sem-acquire.php
     *
     * @return true on success, false on failure.
     */
    public function lock()
    {
        if(sem_acquire($this->sem))
        {
            $this->locked = true;
        }
        else
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * This method will return whether or not the resource represented by 
     * this instance has already been locked or not.
     * @return true if this instance is locked, false otherwise.
     */
    public function isLocked()
    {
        return $this->locked;
    }
    
    /**
     * This method will unlock the resource represented by this class. If this
     * method is called and the lock method has not been called on this 
     * instance, a PHP warning will be generated as according to the 
     * sem_release documentation.
     * 
     * @see http://www.php.net/manual/en/function.sem-remove.php
     * 
     * @return true on success, false on failure.
     */
    public function unlock()
    {
        if(sem_release($this->sem))
        {
            $this->locked = false;
        }
        else
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * The class destructor. Removes the semaphore for this instance of the 
     * class.
     *
     * @throws RuntimeException if the semaphore cannot be relased. Since this
     *         exception is thrown in the destructor, a PHP fatal error will
     *         occur. Manual cleanup will be required using the ipcs and ipcrm
     *         commands from a system terminal in this event, to remove the 
     *         semaphore.
     */
    public function __destruct()
    {
        if(!sem_remove($this->sem))
        {
            throw new RuntimeException("Unable to release the semaphore with "
                . "resource ID " . $this->resourceID . " for resource " . 
                $this->resourceName . "!");
        }
    }
 }
 
?>