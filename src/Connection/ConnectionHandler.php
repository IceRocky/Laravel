<?php

namespace Livewire\Connection;

use Illuminate\Validation\ValidationException;
use Livewire\LivewireComponentWrapper;
use Livewire\LivewireOutput;
use Livewire\TinyHtmlMinifier;

abstract class ConnectionHandler
{
    public function handle($actionQueue, $syncQueue, $serialized)
    {
        $instance = ComponentHydrator::hydrate($serialized);

        $instance->hashPropertiesForDirtyDetection();

        try {
            foreach ($syncQueue ?? [] as $model => $value) {
                $instance->syncInput($model, $value);
            }

            foreach ($actionQueue as $action) {
                $this->processMessage($action['type'], $action['payload'], $instance);
            }
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
        }

        if ($instance->redirectTo) {
            return ['redirectTo' => $instance->redirectTo];
        }

        $id = $instance->id;
        $dom = $instance->output($errors ?? null);
        $serialized = ComponentHydrator::dehydrate($instance);
        $listeningFor = $instance->getEventsBeingListenedFor();

        $minifier = new TinyHtmlMinifier(['collapse_whitespace' => true]);

        return new LivewireOutput([
            'id' => $id,
            'dom' => $minifier->minify(app('livewire')->injectComponentDataAsHtmlAttributesInRootElement(
                $dom, $id, $listeningFor, $serialized
            )),
            'dirtyInputs' => $instance->getDirtyProperties(),
            'listeningFor' => $listeningFor,
            'serialized' => $serialized,
        ]);
    }

    public function processMessage($type, $data, $instance)
    {
        $instance->updating();

        switch ($type) {
            case 'syncInput':
                $instance->syncInput($data['name'], $data['value']);
                break;
            case 'callMethod':
                $instance->callMethod($data['method'], $data['params']);
                break;
            case 'fireEvent':
                $instance->fireEvent($data['event'], $data['params']);
                break;
            default:
                throw new \Exception('Unrecongnized message type: ' . $type);
                break;
        }

        $instance->updated();
    }
}
