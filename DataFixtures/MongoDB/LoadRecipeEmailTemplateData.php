<?php

namespace Ibtikar\GlanceDashboardBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ibtikar\GlanceDashboardBundle\Document\EmailTemplate;


class LoadRecipeEmailTemplateData implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {


        $autoPublishRecipe = new EmailTemplate();
        $autoPublishRecipe->setName('auto publish recipe');
        $autoPublishRecipe->setSubject('النشرالتلقائي لـ (%shortTitle%)');
        $autoPublishRecipe->setMessage('');
        $autoPublishRecipe->setExtraInfo('<tr>
                                                                    <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #889098; text-align:right; line-height: 24px;font-weight: bold;direction:rtl;">
نود أن نخبرك بأنه تم نشر %type% تلقائيا في الوقت الذي تمت تحديده من قبلكم مسبقاً (%time%) (%date%)
                                                                    </td>
                                                                </tr>');

        $autoPublishRecipe->setTemplate('                <tr>
                                                    <td>

                                                        <!-- start of right column -->
                                                        <table  width="149" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <tr>
                                                                    <td >
                                                                        <table width="149" align="center" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                                            <tbody>
                                                                                <!-- title -->
                                                                                <tr>
                                                                                    <td style="padding:10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 16px; color: %color% ; text-align:right; line-height: 24px;">
الرابط
                                                                                    </td>
                                                                                </tr>
                                                                                <!-- end of title -->

                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- end of right column -->

                                                        <!-- Start of left column -->
                                                        <table width="449" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <!-- content -->
                                                                <tr>
                                                                    <td style="padding: 10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #889098; text-align:right; line-height: 24px;">
<a href="%link%">%link%</a>
</td>
                                                                </tr>
                                                                <!-- end of content -->

                                                            </tbody>
                                                        </table>
                                                        <!-- end of left column -->


                                                    </td>
                                                </tr>

                                                <tr >
                                                    <td bgcolor="#f8f8f8">

                                                        <!-- start of right column -->
                                                        <table width="149" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <tr>
                                                                    <td >
                                                                        <table width="149" align="center" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                                            <tbody>
                                                                                <!-- title -->
                                                                                <tr>
                                                                                    <td style="padding: 10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 16px; color:%color%; text-align:right; line-height: 24px;">
العنوان
                                                                                    </td>
                                                                                </tr>
                                                                                <!-- end of title -->

                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- end of right column -->

                                                        <!-- Start of left column -->
                                                        <table width="449" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <!-- content -->
                                                                <tr>
                                                                    <td style="padding: 10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #889098; text-align:right; line-height: 24px;">
%title%
</td>
                                                                </tr>
                                                                <!-- end of content -->

                                                            </tbody>
                                                        </table>
                                                        <!-- end of left column -->


                                                    </td>
                                                </tr>
                <tr>
                                                    <td>

                                                        <!-- start of right column -->
                                                        <table  width="149" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <tr>
                                                                    <td >
                                                                        <table width="149" align="center" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                                            <tbody>
                                                                                <!-- title -->
                                                                                <tr>
                                                                                    <td style="padding:10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 16px; color: %color%; text-align:right; line-height: 24px;">
الحالة
                                                                                    </td>
                                                                                </tr>
                                                                                <!-- end of title -->

                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- end of right column -->

                                                        <!-- Start of left column -->
                                                        <table width="449" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <!-- content -->
                                                                <tr>
                                                                    <td style="padding: 10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #889098; text-align:right; line-height: 24px;">
%status%
</td>
                                                                </tr>
                                                                <!-- end of content -->

                                                            </tbody>
                                                        </table>
                                                        <!-- end of left column -->


                                                    </td>
                                                </tr>

                                                <tr >
                                                    <td bgcolor="#f8f8f8">

                                                        <!-- start of right column -->
                                                        <table width="149" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <tr>
                                                                    <td >
                                                                        <table width="149" align="center" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                                            <tbody>
                                                                                <!-- title -->
                                                                                <tr>
                                                                                    <td style="padding: 10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 16px; color:%color%; text-align:right; line-height: 24px;">
                                                                                        التاريخ
                                                                                    </td>
                                                                                </tr>
                                                                                <!-- end of title -->

                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- end of right column -->

                                                        <!-- Start of left column -->
                                                        <table width="449" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <!-- content -->
                                                                <tr>
                                                                    <td style="padding: 10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #889098; text-align:right; line-height: 24px;">
%date%
</td>
                                                                </tr>
                                                                <!-- end of content -->

                                                            </tbody>
                                                        </table>
                                                        <!-- end of left column -->


                                                    </td>
                                                </tr>
                                                    <tr>
                                                    <td>

                                                        <!-- start of right column -->
                                                        <table  width="149" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <tr>
                                                                    <td >
                                                                        <table width="149" align="center" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                                            <tbody>
                                                                                <!-- title -->
                                                                                <tr>
                                                                                    <td style="padding:10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 16px; color: %color%; text-align:right; line-height: 24px;">
الوقت
                                                                                    </td>
                                                                                </tr>
                                                                                <!-- end of title -->

                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- end of right column -->

                                                        <!-- Start of left column -->
                                                        <table width="449" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                            <tbody>
                                                                <!-- content -->
                                                                <tr>
                                                                    <td style="padding: 10px 20px;font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #889098; text-align:right; line-height: 24px;">
%time%
</td>
                                                                </tr>
                                                                <!-- end of content -->

                                                            </tbody>
                                                        </table>
                                                        <!-- end of left column -->


                                                    </td>
                                                </tr>
');


        $manager->persist($autoPublishRecipe);





        $manager->flush();
    }
}
