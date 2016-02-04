<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\MilestoneBundle\Form;

use Cantiga\CoreBundle\Repository\ProjectAreaStatusRepository;
use Cantiga\Metamodel\Form\EntityTransformer;
use Cantiga\MilestoneBundle\Repository\ProjectMilestoneRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MilestoneStatusRuleForm extends AbstractType
{
	private $statusRepository;
	private $milestoneRepository;
	
	public function __construct(ProjectAreaStatusRepository $statusRepository, ProjectMilestoneRepository $milestoneRepository)
	{
		$this->statusRepository = $statusRepository;
		$this->milestoneRepository = $milestoneRepository;
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'translation_domain' => 'milestone'
		));
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$statusChoices = $this->statusRepository->getFormChoices();
		$milestoneChoices = $this->milestoneRepository->getFormChoices('Area');
		
		$builder
			->add('name', new TextType, array('label' => 'Name'))
			->add('newStatus', 'choice', array('label' => 'New status', 'required' => true, 'choices' => $statusChoices))
			->add('prevStatus', 'choice', array('label' => 'Previous status', 'required' => true, 'choices' => $statusChoices))
			->add('milestoneMap', 'choice', array('label' => 'Required milestones', 'expanded' => true, 'multiple' => true, 'choices' => $milestoneChoices))
			->add('activationOrder', new NumberType, array('label' => 'Activation order'))
			->add('save', 'submit', array('label' => 'Save'));
		
		$builder->get('newStatus')->addModelTransformer(new EntityTransformer($this->statusRepository));
		$builder->get('prevStatus')->addModelTransformer(new EntityTransformer($this->statusRepository));
	}
	
	public function getName()
	{
		return 'MilestoneStatusRule';
	}
}
