module Search exposing (..)

import Platform.Cmd exposing (..)
import Html exposing (..)
import Html.Attributes exposing (..)
import Html.Attributes.Aria exposing (..)
import Html.Events exposing (..)
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
            [ (.normalizedName, 5.0)
            , (.description, 2.0)
            , (.url, 0.5)
            ]
        , listFields = []
        }

init: List SearchDocument -> (Model, Cmd Message)
init searchDatabase =
    (
        { content = ""
        , searchDatabase = searchDatabase
        , searchIndex = Tuple.first (ElmTextSearch.addDocs searchDatabase searchIndex)
        },
        Cmd.none
    )

type alias Model =
    { content: String
    , searchDatabase: List SearchDocument
    , searchIndex: ElmTextSearch.Index SearchDocument
    }

model: Model
model =
    { content = ""
    , searchDatabase = []
    , searchIndex = searchIndex
    }

type Message =
    Search String
  | Escape

update: Message -> Model -> (Model, Cmd Message)
update message model =
    case message of
        Search newContent ->
            ({ model | content = newContent }, Cmd.none)

        Escape ->
            ({ model | content = "" }, Cmd.none)

view: Model -> Html Message
view model =
    let
        searchResults = Result.map (\x -> List.map Tuple.first (Tuple.second x) ) (ElmTextSearch.search model.content model.searchIndex)
    in
    div []
        [ input [ type_ "search" , id "searchInput", value model.content, placeholder "Search anything…" , autocomplete False, onInput Search, onBlur Escape ] []
        , output [ ariaHidden (String.isEmpty model.content) ]
            [ section [] [ h1 [] [ text ("Search results for “" ++ model.content ++ "”") ] ]
            , case searchResults of
                  Ok searchResults ->
                      ol [ class "list--flat" ]
                          (List.map
                               (\searchResult ->
                                    let
                                        document = Maybe.withDefault emptySearchDocument (find (\l -> .id l == searchResult) model.searchDatabase) 
                                    in
                                        li []
                                            [ a [ href ( .url document ) ]
                                                [ code [] [ text ( .name document ) ] ]
                                            , span [] [ text ( .description document ) ] ] )
                               searchResults)
                  _ ->
                      p [] [ text "No result found, sorry!" ]
            ]
        ]

find: (a -> Bool) -> List a -> Maybe a
find predicate list =
    case list of
        [] ->
            Nothing

        first::rest ->
            if predicate first then
                Just first
            else
                find predicate rest

subscriptions: Model -> Sub Message
subscriptions model =
    Sub.none

type alias SearchDocument =
    { id: String
    , name: String
    , normalizedName: String
    , description: String
    , url: String
    }

emptySearchDocument =
    { description = "(unknown)"
    , name = "(unknown)"
    , normalizedName = "(unknown)"
    , url = ""
    , id = ""
    }
