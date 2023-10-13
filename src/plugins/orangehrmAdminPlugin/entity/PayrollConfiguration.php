<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PayrollConfiguration
 *
 * @ORM\Table("ohrm_basic_configuration")
 * @ORM\Entity
 */
class PayrollConfiguration
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;


    /**
     * @var float|null
     *
     * @ORM\Column(name="pf_employee", type="float")
     */
    private ?float $pfEmployee;

    /**
     * @var float|null
     *
     * @ORM\Column(name="pf_employer", type="float")
     */
    private ?float $pfEmployer;

    /**
     * @var float|null
     *
     * @ORM\Column(name="esic_employee", type="float")
     */
    private ?float $esicEmployee ;

    /**
     * @var float|null
     *
     * @ORM\Column(name="esic_employer", type="float")
     */
    private ?float $esicEmployer;

    /**
     * @var float|null
     *
     * @ORM\Column(name="gratuity", type="float")
     */
    private ?float $gratuity;

    /**
     * @var float|null
     *
     * @ORM\Column(name="medical", type="float")
     */
    private ?float $medical;

    /**
     * @var float|null
     *
     * @ORM\Column(name="eps_contri", type="float")
     */
    private ?float $epsContri;

    /**
     * @var float|null
     *
     * @ORM\Column(name="epsepf_contri", type="float")
     */
    private ?float $epsepfContri;

     /**
     * @var float|null
     *
     * @ORM\Column(name="tds_dedu", type="float")
     */
    private ?float $tdsDedu;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return float|null
     */
    public function getPfEmployee(): ?float
    {
        return $this->pfEmployee;
    }

    /**
     * @param float|null $pfEmployee
     */
    public function setPfEmployee(?float $pfEmployee): void
    {
        $this->pfEmployee = $pfEmployee;
    }

    /**
     * @return float
     */
    public function getPfEmployer(): float
    {
        return $this->pfEmployer;
    }

    /**
     * @param float $pfEmployer
     */
    public function setPfEmployer(float $pfEmployer): void
    {
        $this->pfEmployer = $pfEmployer;
    }

    /**
     * @return float|null
     */
    public function getEsicEmployee(): ?float
    {
        return $this->esicEmployee;
    }

    /**
     * @param float|null $esicEmployee
     */
    public function setEsicEmployee(?float $esicEmployee): void
    {
        $this->esicEmployee = $esicEmployee;
    }

    /**
     * @return float|null
     */
    public function getEsicEmployer(): ?float
    {
        return $this->esicEmployer;
    }

    /**
     * @param float|null $esicEmployer
     */
    public function setEsicEmployer(?float $esicEmployer): void
    {
        $this->esicEmployer = $esicEmployer;
    }

    /**
     * @return float|null
     */
    public function getGratuity(): ?float
    {
        return $this->gratuity;
    }

    /**
     * @param float|null $gratuity
     */
    public function setGratuity(?float $gratuity): void
    {
        $this->gratuity = $gratuity;
    }

    /**
     * @return float|null
     */
    public function getMedical(): ?float
    {
        return $this->medical;
    }

    /**
     * @param float|null $medical
     */
    public function setMedical(?float $medical): void
    {
        $this->medical = $medical;
    }

    /**
     * @return float|null
     */
    public function getEpsContri(): ?float
    {
        return $this->epsContri;
    }

    /**
     * @param float|null $epsContri
     */
    public function setEpsContri(?float $epsContri): void
    {
        $this->epsContri = $epsContri;
    }

    /**
     * @return float|null
     */
    public function getEpsepfContri(): ?float
    {
        return $this->epsepfContri;
    }

    /**
     * @param float|null $epsepfContri
     */
    public function setEpsepfContri(?float $epsepfContri): void
    {
        $this->epsepfContri = $epsepfContri;
    }

    /**
     * @return float|null
     */
    public function getTdsDedu(): ?float
    {
        return $this->tdsDedu;
    }

    /**
     * @param float|null $tdsDedu
     */
    public function setTdsDedu(?float $tdsDedu): void
    {
        $this->tdsDedu = $tdsDedu;
    }
}
