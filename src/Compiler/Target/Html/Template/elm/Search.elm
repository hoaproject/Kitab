module Search exposing (..)

import Platform.Cmd exposing (..)
--import Html exposing (Html, div, input, output, section, h1, text)
import Html exposing (..)
import Html.Attributes exposing (..)
import Html.Attributes.Aria exposing (..)
import Html.Events exposing (onInput)
import ElmTextSearch

main =
    Html.programWithFlags
        { init = init
        , view = view
        , update = update
        , subscriptions = subscriptions
        }


searchIndex: ElmTextSearch.Index SearchDocument
searchIndex =
    ElmTextSearch.new
        { ref = .id
        , fields =
            [ (.name, 5.0)
            , (.description, 2.0)
            , (.related, 1.0)
            , (.url, 0.5)
            ]
        , listFields = []
        }

init: List SearchDocument -> (Model, Cmd Message)
init searchItems =
    (
        { content = ""
        , searchIndex = Tuple.first (ElmTextSearch.addDocs searchItems searchIndex)
        },
        Cmd.none
    )

type alias Model =
    { content: String
    , searchIndex: ElmTextSearch.Index SearchDocument
    }

model: Model
model =
    { content = ""
    , searchIndex = searchIndex
    }

type Message =
    Search String

update: Message -> Model -> (Model, Cmd Message)
update message model =
    case message of
        Search newContent ->
            ({ model | content = newContent }, Cmd.none)

view: Model -> Html Message
view model =
    let
        searchResults = Result.map Tuple.second (ElmTextSearch.search model.content model.searchIndex)
    in
    div []
        [ input [ type_ "search" , id "searchInput", placeholder "Search anything…" , autocomplete False, onInput Search ] []
        , output [ ariaHidden (String.isEmpty model.content) ]
            [ section [] [ h1 [] [ text ("Search results for “" ++ model.content ++ "”") ] ]
            , div [] [ text ( "Result of searching for \"explanations\" is " ++ (toString searchResults)) ]
            ]
        ]

subscriptions : Model -> Sub Message
subscriptions model =
    Sub.none

type alias SearchDocument =
    { id: String
    , name: String
    , description: String
    , related: String
    , url: String
    }
