<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Tomasz Jedrzejewski.
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

namespace Cantiga\Components\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A selector for icons.
 */
class IconChoiceType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'choices' => array(
				'Cog' => 'fa-cog',
				'Comment' => 'fa-comment',
				'Woman' => 'fa-female',
				'Flag' => 'fa-flag',
				'Flash' => 'fa-flash',
				'Heart' => 'fa-heart',
				'Info' => 'fa-info',
				'Laptop' => 'fa-laptop',
				'Lightbulb' => 'fa-lightbulb-o',
				'Man' => 'fa-male',
				'Map' => 'fa-map',
				'Microphone' => 'fa-microphone',
				'Mobile phone' => 'fa-mobile',
				'Music' => 'fa-music',
				'Photo' => 'fa-photo',
				'Plane' => 'fa-plane',
				'Question' => 'fa-question',
				'Trophy' => 'fa-trophy',
				'University' => 'fa-university',
			)
		));
	}

	public function getParent()
	{
		return ChoiceType::class;
	}

}