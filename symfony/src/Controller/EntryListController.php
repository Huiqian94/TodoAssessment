<?php

namespace App\Controller;

use App\Entity\EntryList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use DateTime;

/**
 * @Route("/api", name="api_")
 */
class EntryListController extends AbstractController {
    // GET Methods
    /**
     * @Route("/entry/list", name="retrieveAllEntries", methods={"GET"})
     */
    public function retrieve(PersistenceManagerRegistry $doctrine): Response {
        $objects = $doctrine
            ->getRepository(EntryList::class)
            ->findAll();
        
        $data = [];
 
        foreach ($objects as $object) {
           $data[] = [
               'id' => $object->getId(),
               'description' => $object->getDescription(),
               'created_on' => $object->getCreatedOn(),
               'updated_on' => $object->getUpdatedOn()
           ];
        }
 
        return $this->json($data);
    }

        /**
     * @Route("/entry/list/{id}", name="retrieveEntryById", methods={"GET"})
     */
    public function retrieveById(int $id, PersistenceManagerRegistry $doctrine): Response {
        $object = $doctrine()
            ->getRepository(EntryList::class)
            ->find($id);
 
        if (!$object) {
            return $this->json('No entry found for id ' . $id, 404);
        }
 
        $data =  [
            'id' => $object->getId(),
            'description' => $object->getDescription(),
            'created_on' => $existingEntry->getCreatedOn(),
            'updated_on' => $existingEntry->getUpdatedOn()
        ];
         
        return $this->json($data);
    }

    // PPOST/PUT Methods
    /**
     * @Route("/entry/list", name="createEntry", methods={"POST"})
     */
    public function save(Request $request, PersistenceManagerRegistry $doctrine): Response {
        $entityManager = $doctrine->getManager();
        $currentDateTime = new DateTime();

        if (empty($request->query->get('description'))) {
            return $this->json('Missing parameter: description', Response::HTTP_BAD_REQUEST);
        }
        
        $entry = new EntryList();
        $entry->setDescription($request->query->get('description'));
        $entry->setCreatedOn($currentDateTime);
        $entry->setUpdatedOn($currentDateTime);

        $entityManager->persist($entry);
        $entityManager->flush();
 
        return $this->json('Created new project successfully with id ' . $entry->getId());
    }

    /**
     * @Route("/entry/list/{id}", name="updateEntry", methods={"PUT"})
     */
    public function edit(Request $request, int $id, PersistenceManagerRegistry $doctrine): Response {
        $entityManager = $doctrine->getManager();
        $existingEntry = $entityManager->getRepository(EntryList::class)->find($id);
        
        if (!$existingEntry instanceof EntryList) {
            return $this->json('No entry found for id ' . $id, 404);
        }
 
        $existingEntry->setDescription($request->query->get('description'));
        $existingEntry->setUpdatedOn(new DateTime());
        $entityManager->flush();
 
        $data =  [
            'id' => $existingEntry->getId(),
            'description' => $existingEntry->getDescription(),
            'created_on' => $existingEntry->getCreatedOn(),
            'updated_on' => $existingEntry->getUpdatedOn()
        ];
         
        return $this->json($data);
    }

    // DELETE Methods
    /**
     * @Route("/entry/list/{id}", name="deleteEntry", methods={"DELETE"})
     */
    public function delete(Request $request, int $id, PersistenceManagerRegistry $doctrine): Response {
        $isHardDelete = false;
        if ($request->query->get('is_hard_delete') == 1) {
            $isHardDelete = true;
        }

        $entityManager = $doctrine->getManager();
        $existingEntry = $entityManager->getRepository(EntryList::class)->find($id);
 
        if (!$existingEntry instanceof EntryList) {
            return $this->json('No entry found for id ' . $id, 404);
        }

        if ($isHardDelete) {
            $entityManager->remove($existingEntry);
            $entityManager->flush();

            return $this->json('Deleted a entry successfully with id ' . $id);
        } else {
            $currentDateTime = new DateTime();
            $existingEntry->setUpdatedOn($currentDateTime);
            $existingEntry->setDeletedOn($currentDateTime);
            $entityManager->flush();
     
            $data =  [
                'id' => $existingEntry->getId(),
                'description' => $existingEntry->getDescription(),
                'created_on' => $existingEntry->getCreatedOn(),
                'updated_on' => $existingEntry->getUpdatedOn(),
                'deleted_on' => $existingEntry->getDeletedOn()
            ];

            return $this->json($data);
        }
    }
}
