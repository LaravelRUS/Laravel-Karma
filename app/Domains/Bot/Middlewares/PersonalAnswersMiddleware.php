<?php
namespace Domains\Bot\Middlewares;

use Interfaces\Gitter\Ai;
use Domains\Message;
use Interfaces\Gitter\Karma\Validator;
use Domains\Middleware\MiddlewareInterface;
use Domains\User;
use Illuminate\Support\Str;

/**
 * Class PersonalAnswersMiddleware
 */
class PersonalAnswersMiddleware implements MiddlewareInterface
{
    /**
     * @var Ai
     */
    protected $ai;

    /**
     * PersonalAnswersMiddleware constructor.
     *
     * @param Ai $ai
     */
    public function __construct(Ai $ai)
    {
        $this->ai = $ai;
    }

    /**
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message)
    {
        if ($message->user->isBot()) {
            return $message;
        }

        $noMentions = !count($message->mentions);

        // Personal message
        $isBotMention = $message->hasMention(function(User $user) {
            return $user->isBot();
        });

        if ($isBotMention || $noMentions) {
            // Hello all
            $isHello = Str::contains($message->text_without_special_chars, trans('personal.hello_query'));

            if ($isHello) {
                $id = array_rand(trans('personal.hello'));

                $message->italic(trans('personal.hello.' . $id, [
                    'user' => $message->user->login
                ]));
            }
        }

        if ($isBotMention && Str::contains($message->text_without_special_chars, ['кто ты', 'о себе'])) {
            $message->answer(
                '*Привет, я бот. И я написан на:*' . "\n" .
                '* - PHP ' . phpversion()  . "*\n" .
                '* - Laravel ' . \App::version()  . "*\n" .
                '* - Плюс всякие шняжки, вроде react, guzzle, doctrine, php-ds...' . "*\n\n" .
                '*Моё максимальное потребление памяти за всё время жизни ' . number_format(memory_get_peak_usage(true) / 1024 / 1024, 3) . "Mb.*\n" .
                '*Внутри меня обитают расширения: ' . implode(', ', get_loaded_extensions()) . ".*\n" .
                '*Для сайта использую KnockoutJS и EcmaScript 2016.' . "*\n" .
                '*Обитаю в чатах: ' . implode(', ', array_keys(config('gitter.rooms'))) . ",\n" .
                '*а мои внутренности :hear_no_evil: можно добыть тут https://github.com/LaravelRUS/KarmaBot и ' .
                    'обсудить вот тут https://gitter.im/LaravelRUS/GitterBot' . "*\n" .
                '*А чем я занимаюсь можно увидеть тут [https://karma.laravel.su](http://karma.yiiframework.ru)' .
                    ' или тут [http://karma.yiiframework.ru](http://karma.yiiframework.ru) *'
            );
        }

        if (!count($message->mentions)) {
            // Question
            $isQuestion = in_array($message->text_without_special_chars, [
                'можно задать вопрос',
                'хочу задать вопрос',
                'есть кто',
                'есть кто живой',
                'кто может помочь',
                'помогите пожалуйста'
            ], true);

            if ($isQuestion) {
                $message->italic(sprintf('@%s, и какой ответ ты ожидаешь услышать?', $message->user->login));
            }

            // Question
            $isCats = Str::contains($message->text_without_special_chars, ['котаны']);
            if ($isCats) {
                $message->italic(sprintf('@%s, а не поехать ли тебе в Пензу с котанами?', $message->user->login));
            }

            // Question
            $isPenza = Str::contains(mb_strtolower($message->text_without_special_chars), ['пенза']);
            if ($isPenza) {
                $message->italic(sprintf('@%s, а не приехать ли тебе обратно с котанами?', $message->user->login));
            }

            // Question
            $isPolitics = Str::contains($message->text_without_special_chars, [
                'яровая',
                'пакет яровой',
                'пакетом яровой',
                'пакете яровой',
                'пакету яровой',
                'роксомнадзор',
                'битрикс',
                'мизулина'
            ]);

            if ($isPolitics) {
                $message->italic(sprintf('@%s, :see_no_evil: :fire: ', $message->user->login));
            }

            $isRules = in_array($message->text_without_special_chars, [
                'правила чата',
                'правила',
                'как себя вести',
                'читай правила',
                '9 кругов'
            ], true);

            if ($isRules) {
                $message->italic(sprintf('@%s, [url=http://laravel.su/articles/nine-circles-of-chat]In rules we trust[/url]', $message->user->login));
            }

            $isBan = in_array($message->text_without_special_chars, [
                'банхаммер',
                'хаммер',
                'бан',
            ], true);

            if ($isBan) {
                $message->italic(sprintf(
                    '@%s, тебе выданы ' . str_repeat(' :hammer: ', random_int(1, 9)) . ' на 0.' . random_int(1, 9) . ' секунды. Наслаждайся ;)',
                    $message->user->login
                ));
            }

            $isPolitics = in_array($message->text_without_special_chars, [
                'майдан',
                'революция',
                'битрикс',
                'yii',
                'wordpress',
                'вордпресс',
                'laravel',
                'ларавель',
                'йии',
            ], true);

            if ($isPolitics) {
                $message->italic(sprintf(
                    '@%s, за ' . $message->text_without_special_chars . '! ' . str_repeat(' :monkey: ', random_int(1, 9)),
                    $message->user->login
                ));
            }


            if (preg_match('/^[а-кА-К][0-9]$/isu', $message->text_without_special_chars)) {
                $message->italic(sprintf('@%s, %s', $message->user->login, ['мимо', 'ранил', 'убил'][random_int(0, 2)]));

                $char = (range('А', 'К')[random_int(0, 9)]) . '-' . random_int(1, 10);
                $message->italic(sprintf('@%s, %s?', $message->user->login, $char));
            }
        }

        return $message;
    }
}
