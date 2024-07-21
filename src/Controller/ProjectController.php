<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Project;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

header('Access-Control-Allow-Origin: *');

class ProjectController extends AbstractController
{
    public function projects()
    {
        $projects = $this->getDoctrine()->getRepository(Project::class)->findAll();

        foreach($projects as $key => $project){
            $projects[$key] =  [
                    'id'=>$project->getId(),
                    'name'=>$project->getName(),
                    'description' => $project->getDescription(),
                    'date' => $project->getDate(),
                    'donationExpectation' => $project->getDonationExpectation(),
                    'author' => $project->getAuthor(),
                    'categories' => $project->getCategories(),
                    'donations'=> $project->getDonations(),
                    'total' => $project->getTotal()
            ];
           
        }

        return new JsonResponse($projects, 200);
    }

    public function addProject(Request $request, TokenStorageInterface $tokenStorage)
    {
        $request = json_decode($request->getContent(), true);
        $loggedUserEmail = $tokenStorage->getToken()->getUserName();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $author = $repository->findOneBy([
            'email' => $loggedUserEmail,
        ]);
      
        $data = [
            'name' => $request['name'],
            'description' => $request['description'],
            'donationExpectation' => $request['donationExpectation'],
        ];

        $validator = Validation::createValidator();
        $constraint = new Assert\Collection(array(
            // the keys correspond to the keys in the input array
            'name' => new Assert\Length(array('min' => 1)),
            'description' => new Assert\Length(array('min' => 1)),
            'donationExpectation' => new Assert\Length(array('min' => 1)),
        ));
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            return new JsonResponse(["error" => (string)$violations], 500);
        }
        $name = $data['name'];
        $description = $data['description'];
        $donationExpectation = $data['donationExpectation'];
        $author = array(
            'id'=>$author->getId(),
            'firstName'=>$author->getFirstName(),
            'lastName' => $author->getLastName(),
            'userEmail' => $author->getEmail()
        );
        $categories = $request['categories'];

        $project = new Project();
        $project
            ->setName($name)
            ->setDescription($description)
            ->setDonationExpectation($donationExpectation)
            ->setAuthor($author)
            ->setCategories($categories)
            ->onPrePersist()
        ;

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($project);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new JsonResponse(["success" => $project->getName(). " has been registered!"], 200);
    }

    public function deleteProject(int $id)

    {
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $project = $repository->findOneBy([
            'id' => $id
        ]);
        

        if (!$project) {
            return new JsonResponse(["error" => 'Project not exists'], 500);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($project);
            $entityManager->flush();
            return new Response(sprintf('%s successfully removed.', $project->getName()));
        }
    }

    public function editProject(int $id, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $request = json_decode($request->getContent(), true);

        try {
            $data = [];
            $validateData = [];

            if ($id) {
                $repository = $this->getDoctrine()->getRepository(Project::class);
                $project = $repository->findOneBy([
                    'id' => $id,
                ]);

                if (!$id) {
                    return new JsonResponse(["error" => 'User not exists'], 500);
                }
            } else {
                return new JsonResponse(["error" => 'Please set param project ID in route'], 500);
            }

            if ($request['name']) {
                $data['name'] = $request['name'];
                $validateData['name'] = new Assert\Length(array('min' => 1));
                $project->setName($data['name']);
            }

            if ($request['description']) {
                $data['description'] = $request['description'];
                $validateData['description'] = new Assert\Length(array('min' => 1));
                $project->setDescription($data['description']);
            }


            if ($request['donationExpectation']) {
                $data['donationExpectation'] = $request['donationExpectation'];
                $validateData['donationExpectation'] = new Assert\Length(array('min' => 1));
                $project->setDonationExpectation($data['donationExpectation']);
            }
            

            if (!empty($validateData)) {
                $validator  = Validation::createValidator();
                $constraint = new Assert\Collection($validateData);

                $violations = $validator->validate($data, $constraint);
                if ($violations->count() > 0) {
                    return new JsonResponse(["error" => (string)$violations], 500);
                }
            }
            
            $categories = $request['categories'];

            if ($categories) {
                $project->setCategories($categories);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new Response(sprintf('%s already upadeted!', $project->getName()));
    }

    public function getProjectById(string $id)
    {
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $project = $repository->findOneBy([
            'id' => $id,
        ]);

        if (!$project) {
            return new JsonResponse(["error" => 'Project not exists'], 500);
        }

        $data = [
            'id'=>$project->getId(),
            'name'=>$project->getName(),
            'description' => $project->getDescription(),
            'date' => $project->getDate(),
            'donationExpectation' => $project->getDonationExpectation(),
            'author' => $project->getAuthor(),
            'categories' => $project->getCategories(),
            'donations'=> $project->getDonations(),
            'total' => $project->getTotal()
        ];

        return new JsonResponse($data, 200);
    }

    
    public function getAllDonationById(int $projectId)
    {
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $project = $repository->findOneBy([
            'id' => $projectId,
        ]);

        if (!$project) {
            return new JsonResponse(["error" => 'Project not exists'], 500);
        }
        return new JsonResponse($project->getDonations(), 200);
    }

    public function addDonation(int $projectId, Request $request, TokenStorageInterface $tokenStorage){
        $request = json_decode($request->getContent(), true);
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $project = $repository->findOneBy([
            'id' => $projectId,
        ]);


        $loggedUserEmail = $tokenStorage->getToken()->getUserName();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $author = $repository->findOneBy([
            'email' => $loggedUserEmail,
        ]);

        if (!$project) {
            return new JsonResponse(["error" => 'Project not exists'], 500);
        }

        $author = array(
            'id'=>$author->getId(),
            'FirstName'=>$author->getLastName(),
            'LastName' => $author->getFirstName(),
        );
        date_default_timezone_set('America/Sao_Paulo');
        $donation = array(
            'donateValue'=> $request['donationValue'],
            'author'=> $author,
            'date'=> $project->getDate(),
            'donateDate'=> date('d/m/Y H:i:s', time())
        );
        

        $total = $project->getTotal() + $request['donationValue'];
        $project->setTotal($total);

        $project->setDonations(($donation));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return new Response(sprintf('%s successfully donation.', $project->getName()));
    }
}