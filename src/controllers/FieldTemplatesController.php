<?php
namespace fruitstudios\colorit\controllers;

use fruitstudios\colorit\Colorit;
use fruitstudios\colorit\models\FieldTemplate;

use Craft;
use craft\web\Controller;
use craft\helpers\StringHelper;

use yii\web\Response;

class FieldTemplatesController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        $fieldTemplates = Colorit::$plugin->getFieldTemplates()->getAllFieldTemplates();

        return $this->renderTemplate('colorit/settings/fieldtemplates/index', compact('fieldTemplates'));
    }

    public function actionEdit(int $fieldTemplateId = null, FieldTemplate $fieldTemplate = null): Response
    {
        if (!$fieldTemplate)
        {
            if ($fieldTemplateId)
            {
                $fieldTemplate = Colorit::$plugin->getFieldTemplates()->getFieldTemplateById($fieldTemplateId);
                if (!$fieldTemplate)
                {
                    throw new HttpException(404);
                }
            }
            else
            {
                $fieldTemplate = new FieldTemplate();
            }
        }

        $isNewFieldTemplate = !$fieldTemplate->id;

        $allFieldTemplatesTypes = Colorit::$plugin->getFieldTemplates()->getAllFieldTemplateTypes();
        $fieldTemplateTypeOptions = [];
        foreach ($allFieldTemplatesTypes as $class) {
            $fieldTemplateTypeOptions[] = [
                'value' => $class,
                'label' => $class::displayName(),
            ];
        }

        if($isNewFieldTemplate && !$fieldTemplate->type)
        {
            $fieldTemplate->type = $allFieldTemplatesTypes[0];
        }

        return $this->renderTemplate('colorit/settings/fieldtemplates/_edit', [
            'isNewFieldTemplate' => $isNewFieldTemplate,
            'fieldTemplate' => $fieldTemplate,
            'allFieldTemplatesTypes' => $allFieldTemplatesTypes,
            'fieldTemplateTypeOptions' => $fieldTemplateTypeOptions,
        ]);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $fieldTemplatesService = Colorit::$plugin->getFieldTemplates();
        $request = Craft::$app->getRequest();
        $type = $request->getRequiredBodyParam('type');

        $fieldTemplate = $fieldTemplatesService->createFieldTemplate([
            'type' => $type,
            'id' => $request->getBodyParam('fieldTemplateId'),
            'name' => $request->getBodyParam('name'),
            'settings' => $request->getBodyParam('types.'.$type),
        ]);

        if (!Colorit::$plugin->getFieldTemplates()->saveFieldTemplate($fieldTemplate)) {
            Craft::$app->getSession()->setError(Craft::t('colorit', 'Couldn’t save field template.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'fieldTemplate' => $fieldTemplate
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('colorit', 'Field template saved.'));

        return $this->redirectToPostedUrl();
    }

    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        if (Colorit::$plugin->getFieldTemplates()->deleteFieldTemplateById($id))
        {
            return $this->asJson(['success' => true]);
        }
        return $this->asErrorJson(Craft::t('colorit', 'Could not delete field template'));
    }

    // Private Methods
    // =========================================================================

    private function _getFieldTemplateModel(string $type, array $attributes = [])
    {
        try {
            $fieldTemplate = Craft::createObject($type);
            return Craft::configure($fieldTemplate, $attributes);
        } catch(ErrorException $exception) {
            $error = $exception->getMessage();
            return false;
        }
    }

}
